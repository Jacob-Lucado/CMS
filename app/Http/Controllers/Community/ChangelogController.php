<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\ChangelogEntry;
use Illuminate\View\View;

class ChangelogController extends Controller
{
    public function __invoke(): View
    {
        return view('changelog', [
            'entries' => ChangelogEntry::query()->where('published', true)->latest('published_at')->paginate(15),
        ]);
    }
}
