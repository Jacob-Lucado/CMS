<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $riddle_id
 * @property int $user_id
 */
class WebsiteRiddleSolve extends Model
{
    protected $table = 'website_riddle_solves';

    protected $guarded = ['id'];
}
