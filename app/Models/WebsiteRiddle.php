<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $question
 * @property string|null $hint
 * @property string $answer Several accepted answers can be separated by a pipe.
 * @property int $reward_credits
 * @property \Illuminate\Support\Carbon $active_on
 * @property bool $published
 */
class WebsiteRiddle extends Model
{
    protected $table = 'website_riddles';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['active_on' => 'date', 'published' => 'boolean'];
    }

    /** @return HasMany<WebsiteRiddleSolve, $this> */
    public function solves(): HasMany
    {
        return $this->hasMany(WebsiteRiddleSolve::class, 'riddle_id');
    }

    public static function today(): ?self
    {
        return static::query()->where('published', true)->whereDate('active_on', today())->latest('id')->first();
    }

    public function accepts(string $guess): bool
    {
        $normalize = fn (string $text): string => preg_replace('/[^a-z0-9]+/', '', mb_strtolower($text)) ?? '';

        foreach (explode('|', $this->answer) as $answer) {
            if ($normalize($answer) !== '' && $normalize($answer) === $normalize($guess)) {
                return true;
            }
        }

        return false;
    }
}
