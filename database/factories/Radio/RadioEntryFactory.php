<?php

namespace Database\Factories\Radio;

use App\Models\Radio\RadioEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RadioEntry> */
class RadioEntryFactory extends Factory
{
    protected $model = RadioEntry::class;

    public function definition(): array
    {
        $slug = fake()->unique()->slug(2);

        return [
            'kind' => RadioEntry::STATION,
            'status' => RadioEntry::APPROVED,
            'title' => fake()->words(3, true),
            'subtitle' => 'Chill',
            'url' => "https://radio.example.com/{$slug}",
            'stream' => "https://radio.example.com/{$slug}.mp3",
            'user_id' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => RadioEntry::PENDING]);
    }

    public function track(): static
    {
        return $this->state(fn () => [
            'kind' => RadioEntry::TRACK,
            'url' => 'https://soundcloud.com/artist/' . fake()->unique()->slug(2),
            'stream' => 'https://api.soundcloud.com/tracks/' . fake()->unique()->numberBetween(1000, 9999999),
        ]);
    }
}
