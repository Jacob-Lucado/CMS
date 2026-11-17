<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A redeemable code from the emulator's catalog_vouchers table.
 *
 * @property string $voucher
 * @property string $type
 * @property int $value
 * @property int $current_uses
 * @property int $max_uses
 * @property bool|null $enabled
 */
class Voucher extends Model
{
    protected $table = 'catalog_vouchers';

    protected $primaryKey = 'voucher';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
