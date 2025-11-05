<?php

namespace Modules\Blog\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Blog\Models\Post;

class BlogDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a user for the posts
        $user = User::query()->first();

        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Create 20 posts, 80% published and 20% drafts
        Post::factory()
            ->count(16)
            ->published()
            ->create(['user_id' => $user->id]);

        Post::factory()
            ->count(4)
            ->draft()
            ->create(['user_id' => $user->id]);
    }
}
