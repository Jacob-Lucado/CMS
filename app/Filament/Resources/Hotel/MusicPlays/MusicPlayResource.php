<?php

namespace App\Filament\Resources\Hotel\MusicPlays;

use App\Filament\Concerns\RequiresEmulatorDriver;
use App\Filament\Resources\Hotel\MusicPlays\Pages\ManageMusicPlays;
use App\Models\RoomStreamPlay;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Every song a room started with :music (PlusEMU), newest first.
 */
class MusicPlayResource extends Resource
{
    use RequiresEmulatorDriver;

    protected static ?string $model = RoomStreamPlay::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-musical-note';

    protected static string|\UnitEnum|null $navigationGroup = 'Logs';

    protected static ?string $slug = 'logs/music';

    protected static function requiredEmulatorDriver(): string
    {
        return 'plus';
    }

    public static function getNavigationLabel(): string
    {
        return __('Room music');
    }

    public static function getModelLabel(): string
    {
        return __('song');
    }

    public static function getPluralModelLabel(): string
    {
        return __('songs');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('played_at', 'desc')
            ->columns([
                TextColumn::make('set_by')
                    ->label(__('Started by'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('kind')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'youtube' => 'danger',
                        'soundcloud' => 'warning',
                        default => 'info',
                    }),

                TextColumn::make('url')
                    ->label(__('Link'))
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => Str::limit((string) preg_replace('#^https?://(www\.)?#', '', $state), 60))
                    ->tooltip(fn (RoomStreamPlay $record): string => $record->url)
                    ->url(fn (RoomStreamPlay $record): ?string => str_starts_with($record->url, 'https://') ? $record->url : null, shouldOpenInNewTab: true),

                TextColumn::make('room_id')
                    ->label(__('Room'))
                    ->prefix('#')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('played_at')
                    ->label(__('Played'))
                    ->since()
                    ->tooltip(fn (RoomStreamPlay $record): string => $record->played_at->format('Y-m-d H:i:s') . ' UTC')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label(__('Type'))
                    ->options(['youtube' => 'YouTube', 'soundcloud' => 'SoundCloud', 'audio' => 'Audio stream']),

                Filter::make('today')
                    ->label(__('Today'))
                    ->query(fn (Builder $query): Builder => $query->where('played_at', '>=', now()->startOfDay())),

                Filter::make('week')
                    ->label(__('Last 7 days'))
                    ->query(fn (Builder $query): Builder => $query->where('played_at', '>=', now()->subDays(7))),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMusicPlays::route('/'),
        ];
    }
}
