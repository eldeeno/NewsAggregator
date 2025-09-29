<?php

namespace Database\Factories;

use App\Models\NewsSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'news_source_id' => NewsSource::factory(),
            'external_id' => $this->faker->uuid,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'excerpt' => $this->faker->paragraph,
            'author' => $this->faker->name,
            'category' => $this->faker->randomElement(['technology', 'sports', 'politics', 'entertainment']),
            'url' => $this->faker->url,
            'image_url' => $this->faker->imageUrl(),
            'published_at' => $this->faker->dateTimeBetween('-1 year'),
        ];
    }
}
