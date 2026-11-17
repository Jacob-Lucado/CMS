<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $username
 * @property string $message
 * @property string $status
 * @property string|null $staff_note
 * @property int|null $decided_by
 * @property \Illuminate\Support\Carbon|null $decided_at
 */
class BanAppeal extends Model
{
    protected $table = 'website_ban_appeals';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }
}
