<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $poll_id
 * @property int $user_id
 * @property int $option_index
 */
class WebsitePollVote extends Model
{
    protected $table = 'website_poll_votes';

    protected $guarded = ['id'];
}
