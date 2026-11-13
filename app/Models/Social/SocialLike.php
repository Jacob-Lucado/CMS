<?php

namespace App\Models\Social;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $target_type
 * @property string $target_id
 * @property int $user_id
 */
class SocialLike extends Model
{
    public const UPDATED_AT = null;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $table = 'website_social_likes';

    protected $fillable = ['target_type', 'target_id', 'user_id'];
}
