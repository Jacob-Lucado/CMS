<?php

namespace App\Http\Controllers\Community;

use App\Emulator\Contracts\CatalogRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogBrowserController extends Controller
{
    public function __invoke(Request $request, CatalogRepository $catalog): View
    {
        $pages = $catalog->pages();
        $current = $pages->firstWhere('id', (int) $request->query('p'));

        return view('catalog', [
            'roots' => $pages->whereNull('parent_id')->values(),
            'children' => $pages->whereNotNull('parent_id')->groupBy('parent_id'),
            'current' => $current,
            'offers' => $current ? $catalog->offers($current->id) : collect(),
            'iconBase' => rtrim((string) setting('furniture_icons_path'), '/'),
        ]);
    }
}
