<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\User\Users\UserResource;
use App\Models\Community\WeeklyWinner;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Schema;

class WeeklyWinners extends TableWidget
{
    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '300s';

    public static function canView(): bool
    {
        return Schema::hasTable('website_weekly_winners');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Weekly leaderboard winners'))
            ->query(WeeklyWinner::query()->with('user')->latest('week_start')->orderBy('board')->limit(8))
            ->paginated(false)
            ->recordUrl(fn (WeeklyWinner $record): ?string => $record->user ? UserResource::getUrl('edit', ['record' => $record->user]) : null)
            ->emptyStateHeading(__('No weekly winners yet'))
            ->columns([
                TextColumn::make('week_start')->label(__('Week of'))->date('M j'),
                TextColumn::make('board')
                    ->label(__('Board'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'music' ? __('Top DJ') : __('Most kudos'))
                    ->color(fn (string $state): string => $state === 'music' ? 'primary' : 'success'),
                TextColumn::make('user.username')->label(__('Player'))->weight('bold'),
                TextColumn::make('score')->label(__('Score')),
                TextColumn::make('badge')->label(__('Badge'))->placeholder('-'),
            ]);
    }
}
