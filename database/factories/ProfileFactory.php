<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $username = fake()->unique()->userName();
        return [
            'username'        => $username,
            'name'            => ucfirst($username),
            'bio'             => fake()->sentence(),
            'likes_count'     => fake()->numberBetween(0, 200000),
            'avatar_url'      => 'https://picsum.photos/200',
            'last_scraped_at' => now(),
        ];
    }
}
