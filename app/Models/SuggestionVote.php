<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $suggestion_id
 * @property int $user_id
 */
class SuggestionVote extends Model
{
    protected $table = 'website_suggestion_votes';

    protected $guarded = ['id'];
}
