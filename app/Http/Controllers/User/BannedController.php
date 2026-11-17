<?php

namespace App\Http\Controllers\User;

use App\Emulator\Contracts\BanRepository;
use App\Http\Controllers\Controller;
use App\Models\BanAppeal;
use App\Services\Social\DiscordWebhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BannedController extends Controller
{
    public function __invoke(BanRepository $bans): View
    {
        $user = Auth::user();

        $ban = $bans->activeIpBan((string) request()->ip())
            ?? ($user !== null ? $bans->activeAccountBan($user) : null);

        return view('banned', [
            'ban' => $ban,
            'pending' => $user !== null && BanAppeal::query()->where('user_id', $user->id)->where('status', 'pending')->exists(),
        ]);
    }

    public function appeal(Request $request, DiscordWebhook $discord): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'username' => [$user === null ? 'required' : 'nullable', 'string', 'max:64'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $name = $user?->username ?? trim($data['username']);

        if (BanAppeal::query()->where('username', $name)->where('status', 'pending')->exists()) {
            return back()->with('status', __('Your appeal is already with the team.'));
        }

        BanAppeal::query()->create([
            'user_id' => $user?->id,
            'username' => $name,
            'message' => $data['message'],
            'ip_address' => $request->ip(),
        ]);

        $discord->send(__('New ban appeal'), __(':name sent an appeal. Review it in Housekeeping.', ['name' => $name]), 0xE0A030);

        return back()->with('status', __('Thanks. The team will read your appeal.'));
    }
}
