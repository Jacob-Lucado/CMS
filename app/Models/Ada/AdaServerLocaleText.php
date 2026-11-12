<?php

namespace App\Models\Ada;

use Illuminate\Database\Eloquent\Model;

/**
 * An emulator-side translated string.
 *
 * @property int $id
 * @property string $key
 * @property string $text
 */
class AdaServerLocaleText extends Model
{
    protected $table = 'server_locale_texts';

    protected $guarded = ['id'];

    public $timestamps = false;
}
