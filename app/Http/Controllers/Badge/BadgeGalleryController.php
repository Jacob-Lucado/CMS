<?php

namespace App\Http\Controllers\Badge;

use App\Http\Controllers\Controller;
use App\Models\WebsiteBadge;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BadgeGalleryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $badges = WebsiteBadge::query()
            // Badges that only have a code for a name are not worth showing players yet.
            ->whereColumn('badge_name', '!=', 'badge_key')
            ->where('badge_name', '!=', '')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $query->where('badge_key', 'like', $like)
                    ->orWhere('badge_name', 'like', $like)
                    ->orWhere('badge_description', 'like', $like);
            }))
            ->orderBy('badge_name')
            ->paginate(60)
            ->withQueryString();

        return view('badges', ['badges' => $badges, 'search' => $search]);
    }
}
