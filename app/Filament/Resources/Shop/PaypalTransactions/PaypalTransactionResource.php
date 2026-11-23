<?php

namespace App\Filament\Resources\Shop\PaypalTransactions;

use App\Filament\Resources\Shop\PaypalTransactions\Pages\ListPaypalTransactions;
use App\Models\Shop\WebsitePaypalTransaction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaypalTransactionResource extends Resource
{
    protected static ?string $model = WebsitePaypalTransaction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop Management';

    protected static ?string $slug = 'shop/paypal-transactions';

    public static function getNavigationLabel(): string
    {
        return __('PayPal transactions');
    }

    public static function getModelLabel(): string
    {
        return __('PayPal transaction');
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
                TextColumn::make('description')->label(__('Package'))->limit(40)->searchable(),
                TextColumn::make('amount')->label(__('Amount'))->formatStateUsing(fn ($state, $record) => number_format(((int) $state) / 100, 2) . ' ' . strtoupper((string) $record->currency))->sortable(),
                TextColumn::make('status')->label(__('Status'))->badge()->formatStateUsing(fn ($state) => is_object($state) ? ucfirst(strtolower($state->name)) : (string) $state),
                TextColumn::make('transaction_id')->label(__('Transaction'))->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('credited_at')->label(__('Credited'))->dateTime()->placeholder(__('Not credited'))->sortable(),
                TextColumn::make('created_at')->label(__('Created'))->dateTime()->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPaypalTransactions::route('/')];
    }
}
