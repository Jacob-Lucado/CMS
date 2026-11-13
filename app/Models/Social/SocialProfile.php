<?php

namespace App\Models\Social;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property string|null $background
 * @property list<string>|null $muted
 * @property bool $hide_from_rare_directory
 */
class SocialProfile extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'user_id';

    protected $table = 'website_social_profiles';

    protected $fillable = ['user_id', 'background', 'muted', 'hide_from_rare_directory'];

    protected $attributes = ['hide_from_rare_directory' => false];

    protected function casts(): array
    {
        return ['muted' => 'array', 'hide_from_rare_directory' => 'boolean'];
    }
}
