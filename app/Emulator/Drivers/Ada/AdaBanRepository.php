<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\BanRepository;
use App\Emulator\Data\BanInfo;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Ada keeps address bans and account bans in separate tables, and expresses a
 * permanent ban as a NULL expiry rather than a distant timestamp.
 */
class AdaBanRepository implements BanRepository
{
    public function activeIpBan(string $ip): ?BanInfo
    {
        return $this->toInfo(
            'ip',
            $this->active(DB::table('banned_ip_addresses')->where('ip_address', $ip)),
        );
    }

    public function activeAccountBan(User $user): ?BanInfo
    {
        return $this->toInfo(
            'account',
            $this->active(DB::table('player_bans')->where('player_id', $user->id)),
        );
    }

    private function active(Builder $query): ?object
    {
        return $query
            ->where(fn (Builder $expiry) => $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('id')
            ->first();
    }

    private function toInfo(string $type, ?object $ban): ?BanInfo
    {
        if ($ban === null) {
            return null;
        }

        $expiresAt = data_get($ban, 'expires_at');

        return new BanInfo(
            $type,
            (string) data_get($ban, 'reason'),
            $expiresAt === null ? null : Carbon::parse($expiresAt)->unix(),
        );
    }
}
