<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\WebsiteEvent;
use Illuminate\View\View;

class EventsController extends Controller
{
    public function __invoke(): View
    {
        $events = WebsiteEvent::query()->where('published', true)->orderBy('starts_at')->get();

        return view('events', [
            'upcoming' => $events->where('starts_at', '>=', now()->startOfDay())->values(),
            'past' => $events->where('starts_at', '<', now()->startOfDay())->sortByDesc('starts_at')->take(6)->values(),
        ]);
    }
}
