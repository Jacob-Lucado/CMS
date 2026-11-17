<?php

namespace App\Filament\Resources\User\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'tickets';

    protected static ?string $title = 'Support tickets';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')->label(__('ID')),
                TextColumn::make('title')->label(__('Subject'))->limit(60)->searchable(),
                IconColumn::make('open')->label(__('Open'))->boolean(),
                TextColumn::make('created_at')->label(__('Opened'))->dateTime(),
            ]);
    }
}
