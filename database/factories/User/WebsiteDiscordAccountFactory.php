<?php

namespace Database\Factories\User;

use App\Models\User;
use App\Models\User\WebsiteDiscordAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebsiteDiscordAccount>
 */
class WebsiteDiscordAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'discord_id' => (string) fake()->unique()->numberBetween(100000000000000000, 999999999999999999),
            'discord_username' => fake()->userName(),
            'discord_avatar' => null,
        ];
    }
}
