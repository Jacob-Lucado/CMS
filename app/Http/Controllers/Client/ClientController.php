<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\ClientLaunchService;
use App\Support\AuthenticatedUser;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Serve a game client. The route supplies which one via a default for the
     * "client" parameter (client.nitro, client.flash or client.air).
     */
    public function __invoke(Request $request, string $client): View
    {
        $user = AuthenticatedUser::from($request);

        if ($client === 'air') {
            abort_unless(config('habbo.client.air_enabled'), 404);

            $link = app(ClientLaunchService::class);

            return view('client.air', ['url' => $link->airLink($link->ticket($user, $request->ip() ?: 'unknown'))]);
        }

        $view = match ($client) {
            'flash' => 'client.flash',
            default => 'client.nitro',
        };

        $room = (string) $request->query('room', '');

        return view($view, [
            'sso' => app(ClientLaunchService::class)->ticket($user, $request->ip() ?: 'unknown'),
            'room' => ctype_digit($room) && (int) $room > 0 ? (int) $room : null,
        ]);
    }
}
