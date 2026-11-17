<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One finished trade from the emulator's logs_client_trade table.
 *
 * @property int $id
 * @property string $user_one_items
 * @property string $user_two_items
 */
class TradeLog extends Model
{
    protected $table = 'logs_client_trade';

    public $timestamps = false;

    protected function casts(): array
    {
        return ['timestamp' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, '1id');
    }

    /** @return BelongsTo<User, $this> */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, '2id');
    }
}
