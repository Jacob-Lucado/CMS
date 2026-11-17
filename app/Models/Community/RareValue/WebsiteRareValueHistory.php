<?php

namespace App\Models\Community\RareValue;

use Illuminate\Database\Eloquent\Model;

/**
 * A past price of a rare, written each time the value changes.
 *
 * @property int $rare_value_id
 * @property string|null $credit_value
 * @property string|null $currency_value
 * @property int $currency_type
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class WebsiteRareValueHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'website_rare_value_histories';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
