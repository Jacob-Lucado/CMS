<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $note
 * @property \Illuminate\Support\Carbon $awarded_on
 */
class HallOfFameEntry extends Model
{
    protected $table = 'website_hall_of_fame';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['awarded_on' => 'date'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
