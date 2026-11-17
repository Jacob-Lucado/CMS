<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\HallOfFameEntry;
use Illuminate\View\View;

class HallOfFameController extends Controller
{
    public function __invoke(): View
    {
        return view('hall-of-fame', [
            'entries' => HallOfFameEntry::query()->with('user:id,username,look')->latest('awarded_on')->latest('id')->paginate(18),
        ]);
    }
}
