<?php

namespace App\Filament\Resources\Atom\Tickets\Pages;

use App\Filament\Resources\Atom\Tickets\TicketResource;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;
}
