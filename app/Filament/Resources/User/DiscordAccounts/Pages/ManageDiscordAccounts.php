<?php

namespace App\Filament\Resources\User\DiscordAccounts\Pages;

use App\Filament\Resources\User\DiscordAccounts\DiscordAccountResource;
use Filament\Resources\Pages\ManageRecords;

class ManageDiscordAccounts extends ManageRecords
{
    protected static string $resource = DiscordAccountResource::class;
}
