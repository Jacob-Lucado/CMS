<?php

namespace App\Services\Client;

use App\Models\User;

class ClientLaunchService
{
    public function ticket(User $user, string $ipAddress): string
    {
        $user->update(['ip_current' => $ipAddress]);

        return $user->ssoTicket();
    }

    // An AIR launcher set to this hotel's domain takes the ticket from this link.
    public function airLink(string $sso): string
    {
        return 'habbo://hab?' . http_build_query(['server' => parse_url((string) config('app.url'), PHP_URL_HOST), 'token' => $sso], '', '&', PHP_QUERY_RFC3986);
    }
}
