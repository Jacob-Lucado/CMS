<?php

namespace App\Models\Ada;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Ada's server_settings table holds a single row and declares no primary key.
 * created_at is the only column that identifies it, so it stands in as the key
 * for Filament's edit action. The resource offers no create or delete, which
 * keeps that identity stable.
 *
 * @property string|null $player_welcome_message
 * @property bool $fair_currency_rewards
 * @property Carbon $created_at
 */
class AdaServerSettings extends Model
{
    protected $table = 'server_settings';

    protected $primaryKey = 'created_at';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fair_currency_rewards' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
