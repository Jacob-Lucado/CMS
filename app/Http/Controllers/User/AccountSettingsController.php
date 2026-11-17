<?php

namespace App\Http\Controllers\User;

use App\Actions\User\UpdateAccountSettings;
use App\Emulator\Contracts\PlayerSettingsRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccountSettingsFormRequest;
use App\Services\User\SessionService;
use App\Support\AuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function edit(PlayerSettingsRepository $settings): View
    {
        $user = AuthenticatedUser::current();

        return view('user.settings.account', [
            'user' => $user,
            'canChangeName' => $settings->canChangeName($user),
        ]);
    }

    public function export(Request $request): Response
    {
        $user = $request->user();

        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'username' => $user->username,
                'email' => $user->mail,
                'motto' => $user->motto,
                'look' => $user->look,
                'rank' => $user->rank,
                'created_at' => $user->account_created ? date('c', (int) $user->account_created) : null,
                'last_login' => $user->last_login ? date('c', (int) $user->last_login) : null,
                'credits' => $user->credits,
            ],
            'badges' => $user->emulatorBadges()->get()->map(fn ($badge) => $badge->badge_code ?? $badge->badge_id ?? $badge->code)->filter()->values()->all(),
            'tickets' => $user->tickets()->get(['id', 'title', 'content', 'open', 'created_at'])->toArray(),
            'article_comments' => $user->articleComments()->get(['id', 'comment', 'created_at'])->toArray(),
            'sessions' => $user->sessions()->latest('last_activity')->limit(100)->get(['ip_address', 'user_agent', 'last_activity'])->toArray(),
        ];

        return response()->streamDownload(
            fn () => print json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $user->username . '-data.json',
            ['Content-Type' => 'application/json'],
        );
    }

    public function sessionLogs(Request $request, SessionService $sessionService): View
    {
        $sessions = $sessionService->fetchSessionLogs($request);

        return view('user.settings.session-logs', [
            'logs' => $sessions,
        ]);
    }

    public function update(AccountSettingsFormRequest $request, UpdateAccountSettings $updateAccountSettings): RedirectResponse
    {
        $updateAccountSettings->execute(AuthenticatedUser::from($request), $request->validated());

        return redirect()->route('settings.account.show')->with('success', __('Your account settings has been updated'));
    }
}
