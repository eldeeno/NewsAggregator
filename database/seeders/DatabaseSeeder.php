<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\NewsSource;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create sources
        // NewsSource::factory()->newsapi()->active()->create();
        // NewsSource::factory()->guardian()->active()->create();
        // NewsSource::factory()->nytimes()->active()->create();

        // Article::factory()->count(50)->create();

        $sources = [
            [
                'name' => 'NewsAPI Aggregator',
                'slug' => 'newsapi',
                'api_service' => 'newsapi',
                'config' => ['categories' => ['general', 'technology', 'business']],
                'is_active' => true,
            ],
            [
                'name' => 'The Guardian',
                'slug' => 'guardian',
                'api_service' => 'guardian',
                'config' => ['sections' => ['technology', 'business', 'politics']],
                'is_active' => true,
            ],
            [
                'name' => 'New York Times',
                'slug' => 'nytimes',
                'api_service' => 'nytimes',
                'config' => ['sections' => ['technology', 'science', 'world']],
                'is_active' => true,
            ],
        ];

        foreach ($sources as $source) {
            NewsSource::create($source);
        }
    }
}
