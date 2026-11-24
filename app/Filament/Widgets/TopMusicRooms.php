<?php

namespace App\Filament\Widgets;

use App\Emulator\Emulator;
use App\Models\RoomStreamPlay;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Schema;

class TopMusicRooms extends TableWidget
{
    protected static ?int $sort = 5;

    protected ?string $pollingInterval = '300s';

    public static function canView(): bool
    {
        return Emulator::driver() === 'plus' && Schema::hasTable('room_stream_plays');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Top music rooms this week'))
            ->query(
                RoomStreamPlay::query()
                    ->leftJoin('rooms', 'rooms.id', '=', 'room_stream_plays.room_id')
                    ->where('room_stream_plays.played_at', '>=', now()->subDays(7))
                    ->selectRaw('room_stream_plays.room_id as id, rooms.caption as room, COUNT(*) as plays, COUNT(DISTINCT room_stream_plays.set_by) as djs')
                    ->groupBy('room_stream_plays.room_id', 'rooms.caption')
                    ->orderByDesc('plays')
                    ->orderBy('room_stream_plays.room_id')
                    ->limit(8),
            )
            ->paginated(false)
            ->defaultKeySort(false)
            ->emptyStateHeading(__('No songs played this week'))
            ->columns([
                TextColumn::make('room')->label(__('Room'))->weight('bold')->placeholder(__('Deleted room')),
                TextColumn::make('plays')->label(__('Songs')),
                TextColumn::make('djs')->label(__('DJs')),
            ]);
    }
}
