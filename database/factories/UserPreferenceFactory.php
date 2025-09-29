<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'preferred_sources' => [],
            'preferred_categories' => $this->faker->randomElements(
                ['technology', 'sports', 'politics', 'entertainment', 'business', 'science'],
                2
            ),
            'preferred_authors' => $this->faker->randomElements(
                ['John Doe', 'Jane Smith', 'Mike Johnson', 'Sarah Williams', 'David Brown'],
                2
            ),
        ];
    }
}
