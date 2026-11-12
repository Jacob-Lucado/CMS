<?php

namespace App\Models\Ada;

use App\Emulator\Models\Rank;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Ada's roles table is the rank model on this driver. It stores a name and
 * nothing else, so the presentation fields the CMS renders alongside a rank
 * are exposed as fixed defaults rather than columns.
 *
 * @property int $id
 * @property string|null $name
 * @property-read Collection<int, AdaPermission> $permissions
 * @property-read Collection<int, User> $users
 */
class AdaRole extends Rank
{
    protected $table = 'roles';

    protected $guarded = ['id'];

    public $timestamps = false;

    /** @return BelongsToMany<AdaPermission, $this> */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            AdaPermission::class,
            'roles_permissions',
            'role_id',
            'permission_id',
        );
    }

    /**
     * Ada assigns roles through a pivot; the users.rank mirror only exists so
     * Atom's own queries keep working.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'player_role', 'role_id', 'player_id');
    }

    /** @return Attribute<string|null, never> */
    protected function rankName(): Attribute
    {
        return Attribute::get(function (): ?string {
            $name = $this->getAttribute('name');

            return is_string($name) ? $name : null;
        });
    }

    /** @return Attribute<string, never> */
    protected function badge(): Attribute
    {
        return Attribute::get(fn (): string => '');
    }

    /** @return Attribute<string, never> */
    protected function staffColor(): Attribute
    {
        return Attribute::get(fn (): string => '#327fa8');
    }

    /** @return Attribute<string, never> */
    protected function jobDescription(): Attribute
    {
        return Attribute::get(fn (): string => 'Here to help');
    }

    /** @return Attribute<string, never> */
    protected function staffBackground(): Attribute
    {
        return Attribute::get(fn (): string => 'staff-bg.png');
    }
}
