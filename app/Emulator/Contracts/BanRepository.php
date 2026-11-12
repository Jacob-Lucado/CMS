<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\BanInfo;
use App\Models\User;

/**
 * Answers whether a visitor or account is banned on the emulator database.
 * Arcturus stores bans in one typed table; Ada separates player and IP bans.
 */
interface BanRepository
{
    public function activeIpBan(string $ip): ?BanInfo;

    public function activeAccountBan(User $user): ?BanInfo;
}
