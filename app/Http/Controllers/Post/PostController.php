<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $posts = Post::with('user:id,name,email')
            ->active()
            ->orderByDesc('published_at')
            ->paginate(20);

        return response()->json($posts);
    }

  
    public function create()
    {
        return response('posts.create', 200)
            ->header('Content-Type', 'text/plain');
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = Post::create([
            'user_id'      => $request->user()->id,
            'title'        => $request->input('title'),
            'content'      => $request->input('content'),
            'is_draft'     => $request->boolean('is_draft'),
            'published_at' => $request->input('published_at'),
        ]);

        return response()->json(['data' => $post->load('user:id,name,email')], 201);
    }

    public function show(Post $post): JsonResponse
    {
        if ($post->is_draft || ($post->published_at && $post->published_at->isFuture())) {
            abort(404);
        }

        return response()->json($post->load('user:id,name,email'));
    }

    
    public function edit(Post $post)
    {
        return response('posts.edit', 200)
            ->header('Content-Type', 'text/plain');
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return response()->json(['data' => $post->load('user:id,name,email')]);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }
}
