<?php

namespace App\Filament\Resources\Shop\SquarePayments;

use App\Filament\Resources\Shop\SquarePayments\Pages\ListSquarePayments;
use App\Models\Shop\WebsiteSquarePayment;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SquarePaymentResource extends Resource
{
    protected static ?string $model = WebsiteSquarePayment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop Management';

    protected static ?string $slug = 'shop/square-payments';

    public static function getNavigationLabel(): string
    {
        return __('Square payments');
    }

    public static function getModelLabel(): string
    {
        return __('Square payment');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable(),
                TextColumn::make('user.username')->label(__('Player'))->searchable(),
                TextColumn::make('amount')->label(__('Amount'))->formatStateUsing(fn ($state, $record) => number_format(((int) $state) / 100, 2) . ' ' . strtoupper((string) $record->currency))->sortable(),
                TextColumn::make('status')->label(__('Status'))->badge()->color(fn (string $state) => $state === 'paid' ? 'success' : 'warning'),
                TextColumn::make('reference')->label(__('Reference'))->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('credited_at')->label(__('Credited'))->dateTime()->placeholder(__('Not credited'))->sortable(),
                TextColumn::make('created_at')->label(__('Created'))->dateTime()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(['open' => 'Open', 'paid' => 'Paid'])])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSquarePayments::route('/')];
    }
}
