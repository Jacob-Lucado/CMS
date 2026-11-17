<?php

namespace App\Http\Controllers\Community;

use App\Emulator\Contracts\FurnitureRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FurniBrowserController extends Controller
{
    public function __invoke(Request $request, FurnitureRepository $furniture): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('furni', [
            'items' => $furniture->definitions($search),
            'search' => $search,
            'iconBase' => rtrim((string) setting('furniture_icons_path'), '/'),
        ]);
    }
}
