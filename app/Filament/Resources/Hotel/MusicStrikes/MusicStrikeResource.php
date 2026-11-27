<?php

namespace App\Filament\Resources\Hotel\MusicStrikes;

use App\Filament\Concerns\RequiresEmulatorDriver;
use App\Filament\Resources\Hotel\MusicStrikes\Pages\ManageMusicStrikes;
use App\Models\RoomStreamStrike;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Players who played banned music with :music (PlusEMU). At 3 strikes they lose the command until it is lifted.
 */
class MusicStrikeResource extends Resource
{
    use RequiresEmulatorDriver;

    protected static ?string $model = RoomStreamStrike::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotel';

    protected static ?string $slug = 'hotel/music-strikes';

    protected static function requiredEmulatorDriver(): string
    {
        return 'plus';
    }

    public static function getNavigationLabel(): string
    {
        return __('Music strikes');
    }

    public static function getModelLabel(): string
    {
        return __('music strike');
    }

    public static function getPluralModelLabel(): string
    {
        return __('music strikes');
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
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('user.username')
                    ->label(__('Player'))
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('strikes')
                    ->label(__('Strikes'))
                    ->badge()
                    ->color(fn (int $state): string => $state >= 3 ? 'danger' : 'warning')
                    ->sortable(),

                IconColumn::make('restricted')
                    ->label(__('Lost :music'))
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('gray'),

                TextColumn::make('updated_at')
                    ->label(__('Last strike'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('restricted')
                    ->label(__('Lost :music')),
            ])
            ->recordActions([
                Action::make('lift')
                    ->label(__('Lift'))
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription(__('This clears their strikes and gives :music back.'))
                    ->action(fn (RoomStreamStrike $record) => $record->delete()),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMusicStrikes::route('/'),
        ];
    }
}
