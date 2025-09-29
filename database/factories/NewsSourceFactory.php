<?php

namespace Database\Factories;

use App\Models\NewsSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsSource>
 */
class NewsSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = ['newsapi', 'guardian', 'nytimes'];
        $service = $this->faker->randomElement($services);

        return [
            'name' => $this->faker->company . ' News',
            'slug' => $this->faker->slug,
            'api_service' => $service,
            'config' => [
                'categories' => $this->faker->randomElements(
                    ['technology', 'sports', 'politics', 'entertainment', 'business', 'science'],
                    3
                ),
            ],
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function newsapi(): self
    {
        return $this->state([
            'name' => 'NewsAPI',
            'slug' => 'newsapi',
            'api_service' => 'newsapi',
            'config' => ['categories' => ['general', 'technology', 'business']],
        ]);
    }

    public function guardian(): self
    {
        return $this->state([
            'name' => 'The Guardian',
            'slug' => 'guardian',
            'api_service' => 'guardian',
            'config' => ['sections' => ['technology', 'business', 'politics']],
        ]);
    }

    public function nytimes(): self
    {
        return $this->state([
            'name' => 'New York Times',
            'slug' => 'nytimes',
            'api_service' => 'nytimes',
            'config' => ['sections' => ['technology', 'science', 'world']],
        ]);
    }

    public function active(): self
    {
        return $this->state([
            'is_active' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}
