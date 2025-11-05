<?php

namespace Modules\Blog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Modules\Blog\Models\Post;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run Blog module migrations after RefreshDatabase
        Artisan::call('migrate', [
            '--path' => 'Modules/Blog/database/migrations',
            '--realpath' => true,
        ]);
    }

    public function test_can_view_posts_index(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/blog');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('blog/index', false));
    }

    public function test_can_view_create_post_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/blog/create');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('blog/create', false));
    }

    public function test_can_create_post(): void
    {
        $user = User::factory()->create();

        $postData = [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ];

        $response = $this->actingAs($user)->post('/blog', $postData);

        $response->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'slug' => 'test-post',
            'user_id' => $user->id,
        ]);
    }

    public function test_can_view_single_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/blog/{$post->id}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('blog/show', false)
            ->has('post')
        );
    }

    public function test_can_view_edit_post_page(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/blog/{$post->id}/edit");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('blog/edit', false)
            ->has('post')
        );
    }

    public function test_can_update_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Updated Post Title',
            'content' => 'Updated content for the post.',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ];

        $response = $this->actingAs($user)->put("/blog/{$post->id}", $updateData);

        $response->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Post Title',
            'slug' => 'updated-post-title',
        ]);
    }

    public function test_can_delete_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/blog/{$post->id}");

        $response->assertRedirect('/blog');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_validation_errors_when_creating_post_without_title(): void
    {
        $user = User::factory()->create();

        $postData = [
            'content' => 'Content without title',
        ];

        $response = $this->actingAs($user)->post('/blog', $postData);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_validation_errors_when_creating_post_without_content(): void
    {
        $user = User::factory()->create();

        $postData = [
            'title' => 'Title without content',
        ];

        $response = $this->actingAs($user)->post('/blog', $postData);

        $response->assertSessionHasErrors(['content']);
    }

    public function test_guests_cannot_access_blog(): void
    {
        $response = $this->get('/blog');

        $response->assertRedirect('/login');
    }
}
