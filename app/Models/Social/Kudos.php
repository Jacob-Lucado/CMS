<?php

namespace App\Models\Social;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property string $board
 * @property Carbon $given_on
 */
class Kudos extends Model
{
    protected $table = 'website_kudos';

    protected $fillable = ['from_user_id', 'to_user_id', 'board', 'given_on'];

    protected function casts(): array
    {
        return ['given_on' => 'date'];
    }
}
