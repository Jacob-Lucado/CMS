<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\WebsiteEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventsController extends Controller
{
    public function __invoke(): View
    {
        $events = WebsiteEvent::query()->where('published', true)->withCount('attendees')->orderBy('starts_at')->get();
        $going = auth()->check() ? auth()->user()->eventsGoing()->pluck('website_events.id')->all() : [];

        return view('events', [
            'going' => $going,
            'upcoming' => $events->where('starts_at', '>=', now()->startOfDay())->values(),
            'past' => $events->where('starts_at', '<', now()->startOfDay())->sortByDesc('starts_at')->take(6)->values(),
        ]);
    }

    public function toggle(Request $request, WebsiteEvent $event): RedirectResponse
    {
        abort_unless($event->published && $event->starts_at->isFuture(), 404);

        $request->user()->eventsGoing()->toggle($event->id);

        return back();
    }
}
