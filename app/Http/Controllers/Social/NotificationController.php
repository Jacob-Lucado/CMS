<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Social\SocialProfile;
use App\Models\Social\WebsiteNotification;
use App\Models\User;
use App\Services\Social\Notifier;
use App\Services\Social\SocialFeed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function show(Request $request): View
    {
        $user = $this->viewer($request);

        return view('notifications', [
            'types' => Notifier::TYPES,
            'muted' => SocialProfile::query()->find($user->id)->muted ?? [],
        ]);
    }

    public function feed(Request $request, SocialFeed $feed): JsonResponse
    {
        $user = $this->viewer($request);
        $notifications = WebsiteNotification::query()->with('actor')->where('user_id', $user->id)->latest('id')->limit(50)->get();

        return response()->json([
            'unread' => $notifications->whereNull('read_at')->count(),
            'notifications' => $notifications->map(fn (WebsiteNotification $notification): array => [
                'id' => $notification->id,
                'type' => $notification->type,
                'message' => $notification->message,
                'url' => $notification->url,
                'read' => $notification->read_at !== null,
                'ago' => $notification->created_at->diffForHumans(),
                'actor' => $feed->author($notification->actor),
            ])->values(),
        ])->header('Cache-Control', 'no-store');
    }

    public function read(Request $request): JsonResponse
    {
        $user = $this->viewer($request);
        $id = $request->validate(['id' => ['nullable', 'integer']])['id'] ?? null;

        WebsiteNotification::query()->where('user_id', $user->id)->whereNull('read_at')
            ->when($id, fn ($query) => $query->whereKey($id))
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function clear(Request $request): JsonResponse
    {
        WebsiteNotification::query()->where('user_id', $this->viewer($request)->id)->delete();

        return response()->json(['ok' => true]);
    }

    private function viewer(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
