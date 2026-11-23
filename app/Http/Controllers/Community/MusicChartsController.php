<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Services\Community\MusicChartsService;
use Illuminate\View\View;

class MusicChartsController extends Controller
{
    public function __invoke(MusicChartsService $charts): View
    {
        return view('music-charts', $charts->charts() + ['days' => MusicChartsService::DAYS]);
    }
}
