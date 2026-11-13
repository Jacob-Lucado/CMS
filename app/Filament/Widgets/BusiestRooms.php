<?php

namespace App\Filament\Widgets;

use App\Emulator\Contracts\RoomRepository;
use App\Emulator\Data\RoomActivity;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class BusiestRooms extends Widget
{
    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.busiest-rooms';

    protected ?string $pollingInterval = '30s';

    /** @return Collection<int, RoomActivity> */
    public function rooms(): Collection
    {
        return app(RoomRepository::class)->busiest(8);
    }
}
