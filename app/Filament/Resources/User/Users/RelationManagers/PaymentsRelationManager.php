<?php

namespace App\Filament\Resources\User\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'squarePayments';

    protected static ?string $title = 'Square payments';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label(__('ID')),
                TextColumn::make('amount')->label(__('Amount'))->formatStateUsing(fn ($state, $record) => number_format(((int) $state) / 100, 2) . ' ' . strtoupper((string) $record->currency)),
                TextColumn::make('status')->label(__('Status'))->badge()->color(fn (string $state) => $state === 'paid' ? 'success' : 'warning'),
                TextColumn::make('credited_at')->label(__('Credited'))->dateTime()->placeholder(__('Not credited')),
                TextColumn::make('created_at')->label(__('Created'))->dateTime(),
            ]);
    }
}
