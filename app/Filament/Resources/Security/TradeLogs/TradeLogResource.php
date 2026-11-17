<?php

namespace App\Filament\Resources\Security\TradeLogs;

use App\Filament\Resources\Security\TradeLogs\Pages\ManageTradeLogs;
use App\Models\TradeLog;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TradeLogResource extends Resource
{
    protected static ?string $model = TradeLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Security';

    protected static ?string $slug = 'security/trade-log';

    public static function getNavigationLabel(): string
    {
        return __('Trade log');
    }

    public static function getModelLabel(): string
    {
        return __('Trade');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['userOne:id,username', 'userTwo:id,username']))
            ->defaultSort('timestamp', 'desc')
            ->columns([
                TextColumn::make('timestamp')->label(__('When'))->dateTime()->sortable(),
                TextColumn::make('userOne.username')->label(__('Player one'))->searchable(query: fn ($query, string $search) => $query->whereHas('userOne', fn ($user) => $user->where('username', 'like', "%{$search}%")))->placeholder('-'),
                TextColumn::make('1items')->label(__('Gave'))->wrap()->limit(120),
                TextColumn::make('userTwo.username')->label(__('Player two'))->searchable(query: fn ($query, string $search) => $query->whereHas('userTwo', fn ($user) => $user->where('username', 'like', "%{$search}%")))->placeholder('-'),
                TextColumn::make('2items')->label(__('Gave'))->wrap()->limit(120),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTradeLogs::route('/')];
    }
}
