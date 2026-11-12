<?php

namespace App\Emulator\Drivers\Arcturus;

use App\Emulator\Contracts\PlayerSettingsRepository;
use App\Models\Game\Player\UserSetting;
use App\Models\Game\Player\UserSubscription;
use App\Models\User;

class ArcturusPlayerSettingsRepository implements PlayerSettingsRepository
{
    public function created(User $user): void
    {
        $giveHc = (setting('give_hc_on_register') ?: '0') === '1';

        UserSetting::query()->create([
            'user_id' => $user->id,
            'last_hc_payday' => $giveHc ? now()->addYears(10)->unix() : 0,
        ]);

        if ($giveHc) {
            UserSubscription::query()->insert([
                'user_id' => $user->id,
                'subscription_type' => 'HABBO_CLUB',
                'timestamp_start' => now()->unix(),
                'duration' => (int) (setting('hc_on_register_duration') ?: 0),
                'active' => 1,
            ]);
        }
    }

    public function canChangeName(User $user): bool
    {
        return (bool) UserSetting::query()
            ->where('user_id', $user->id)
            ->value('can_change_name');
    }

    public function setCanChangeName(User $user, bool $allowed): void
    {
        UserSetting::query()
            ->where('user_id', $user->id)
            ->update(['can_change_name' => $allowed ? '1' : '0']);
    }
}
