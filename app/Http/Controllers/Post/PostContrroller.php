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
    /** GET /posts – only ACTIVE, with user, paginate 20, JSON */
    public function index(Request $request): JsonResponse
    {
        $posts = Post::query()
            ->with(['user:id,name,email'])
            ->select(['id','user_id','title','content','is_draft','published_at','created_at','updated_at'])
            ->active()
            ->orderByDesc('published_at')
            ->paginate(20);

        return response()->json($posts);
    }

    /** GET /posts/create – allowed to return a simple string */
    public function create()
    {
        return response('posts.create');
    }

    /** POST /posts – auth via routes, validate via FormRequest, JSON 201 */
    public function store(StorePostRequest $request): JsonResponse
    {
        $data = $request->validated();

        $post = Post::create([
            'title'        => $data['title'],
            'content'      => $data['content'],
            'is_draft'     => $data['is_draft'],
            'published_at' => $data['published_at'] ?? null,
            'user_id'      => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Post created',
            'data'    => $post->load('user:id,name,email'),
        ], 201);
    }

    /** GET /posts/{post} – 404 for draft or scheduled, JSON for active */
    public function show(Post $post): JsonResponse
    {
        if ($post->is_draft || (optional($post->published_at)?->isFuture())) {
            abort(404);
        }

        $post->load('user:id,name,email');
        return response()->json($post);
    }

    /** GET /posts/{post}/edit – allowed to return a simple string */
    public function edit(Post $post)
    {
        return response('posts.edit');
    }

    /** PUT/PATCH /posts/{post} – only author (policy), JSON */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $post->fill($request->validated());
        $post->save();

        return response()->json([
            'message' => 'Post updated',
            'data'    => $post->load('user:id,name,email'),
        ]);
    }

    /** DELETE /posts/{post} – only author (policy), JSON */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }
}
