<?php

namespace Database\Factories\Social;

use App\Models\Social\SocialPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialPost>
 */
class SocialPostFactory extends Factory
{
    protected $model = SocialPost::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => SocialPost::STATUS,
            'body' => fake()->sentence(),
        ];
    }
}
