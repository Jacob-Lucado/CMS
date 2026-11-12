<?php

namespace App\Models\Ada;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A room on Ada's schema.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $owner_id
 * @property-read User|null $owner
 */
class AdaRoom extends Model
{
    protected $table = 'rooms';

    protected $guarded = [];

    public $timestamps = false;

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
