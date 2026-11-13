<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\User\Users\UserResource;
use App\Models\User;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class NewestPlayers extends TableWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Newest players'))
            ->query(User::query()->with('discordAccount')->latest('id')->limit(8))
            ->paginated(false)
            ->recordUrl(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record]))
            ->columns([
                ImageColumn::make('look')
                    ->label('')
                    ->state(fn (User $record): string => setting('avatar_imager') . $record->look . '&headonly=1&head_direction=3&size=s')
                    ->imageHeight(32),
                TextColumn::make('username')->label(__('Player'))->weight('bold'),
                IconColumn::make('discord')
                    ->label(__('Discord'))
                    ->state(fn (User $record): bool => $record->discordAccount !== null)
                    ->boolean(),
                TextColumn::make('online')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? __('Online') : __('Offline'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ]);
    }
}
