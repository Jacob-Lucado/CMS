<?php

namespace App\Models\Ada;

use Illuminate\Database\Eloquent\Model;

/**
 * A named capability Ada grants through roles_permissions.
 *
 * @property int $id
 * @property string|null $name
 */
class AdaPermission extends Model
{
    protected $table = 'permissions';

    protected $guarded = ['id'];

    public $timestamps = false;
}
