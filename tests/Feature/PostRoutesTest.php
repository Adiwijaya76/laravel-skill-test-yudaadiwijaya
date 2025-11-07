<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_only_lists_active_posts_with_user_and_paginates_20(): void
    {
        $author = User::factory()->create();

        // Active (should appear)
        Post::factory()->create([
            'user_id'      => $author->id,
            'is_draft'     => false,
            'published_at' => now()->subHour(),
        ]);

        // Draft (excluded)
        Post::factory()->draft()->create(['user_id' => $author->id]);

        // Scheduled (excluded)
        Post::factory()->scheduled()->create(['user_id' => $author->id]);

        $res = $this->getJson('/posts');

        $res->assertOk()
            ->assertJsonPath('per_page', 20)
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id','user_id','title','content','is_draft','published_at','created_at','updated_at',
                    'user' => ['id','name','email']
                ]],
                'current_page','per_page','total'
            ]);
    }

    public function test_index_paginates_20_items_per_page(): void
    {
        $author = User::factory()->create();

        Post::factory()->count(25)->create([
            'user_id'      => $author->id,
            'is_draft'     => false,
            'published_at' => now()->subMinutes(5),
        ]);

        $this->getJson('/posts?page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 20)
            ->assertJsonCount(20, 'data');

        $this->getJson('/posts?page=2')
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_show_returns_200_for_active_and_404_for_draft_or_scheduled(): void
    {
        $author = User::factory()->create();

        $active = Post::factory()->create([
            'user_id'      => $author->id,
            'is_draft'     => false,
            'published_at' => now()->subMinute(),
        ]);

        $draft     = Post::factory()->draft()->create(['user_id' => $author->id]);
        $scheduled = Post::factory()->scheduled()->create(['user_id' => $author->id]);

        $this->getJson("/posts/{$active->id}")->assertOk();
        $this->getJson("/posts/{$draft->id}")->assertNotFound();
        $this->getJson("/posts/{$scheduled->id}")->assertNotFound();
    }

    public function test_create_route_returns_string_as_allowed_by_brief(): void
    {
        $this->get('/posts/create')
            ->assertOk()
            ->assertSeeText('posts.create');
    }

    public function test_edit_route_returns_string_as_allowed_by_brief(): void
    {
        $post = Post::factory()->create();

        $this->get("/posts/{$post->id}/edit")
            ->assertOk()
            ->assertSeeText('posts.edit');
    }

    public function test_store_requires_authentication_and_creates_post_when_authenticated(): void
    {
        $payload = [
            'title'        => 'Hello World',
            'content'      => 'Body content',
            'is_draft'     => false,
            'published_at' => now()->toISOString(),
        ];

        // Guest (non-JSON) => redirect 302 to login
        $this->post('/posts', $payload)->assertStatus(302);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/posts', $payload)
            ->assertCreated()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.title', 'Hello World')
            ->assertJsonPath('data.is_draft', false);
    }

    public function test_store_validates_payload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/posts', [
                'title'    => '',
                'content'  => '',
                'is_draft' => 'x',
            ])
            ->assertStatus(422);
    }

    public function test_update_only_allowed_for_author(): void
    {
        $author = User::factory()->create();
        $other  = User::factory()->create();

        $post = Post::factory()->create([
            'user_id'      => $author->id,
            'is_draft'     => true,
            'published_at' => now()->addDay(),
        ]);

        // Non-author -> 403
        $this->actingAs($other)
            ->patchJson("/posts/{$post->id}", ['title' => 'X'])
            ->assertForbidden();

        // Author -> OK
        $this->actingAs($author)
            ->patchJson("/posts/{$post->id}", [
                'title'        => 'Updated',
                'is_draft'     => false,
                'published_at' => now()->toISOString(),
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated')
            ->assertJsonPath('data.is_draft', false);
    }

    public function test_delete_only_allowed_for_author(): void
    {
        $author = User::factory()->create();
        $other  = User::factory()->create();

        $post = Post::factory()->create([
            'user_id'      => $author->id,
            'is_draft'     => true,
            'published_at' => null,
        ]);

        // Non-author -> 403
        $this->actingAs($other)
            ->deleteJson("/posts/{$post->id}")
            ->assertForbidden();

        // Author -> OK
        $this->actingAs($author)
            ->deleteJson("/posts/{$post->id}")
            ->assertOk();
    }
}
