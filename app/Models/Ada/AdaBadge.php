<?php

namespace App\Models\Ada;

use Illuminate\Database\Eloquent\Model;

/**
 * A badge definition on Ada's schema; player_badges references it by id.
 *
 * @property int $id
 * @property string|null $code
 */
class AdaBadge extends Model
{
    protected $table = 'badges';

    protected $guarded = ['id'];

    public $timestamps = false;
}
