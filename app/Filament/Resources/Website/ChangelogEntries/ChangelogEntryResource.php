<?php

namespace App\Filament\Resources\Website\ChangelogEntries;

use App\Filament\Resources\Website\ChangelogEntries\Pages\ManageChangelogEntries;
use App\Models\ChangelogEntry;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChangelogEntryResource extends Resource
{
    protected static ?string $model = ChangelogEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/changelog';

    public static function getNavigationLabel(): string
    {
        return __('Changelog');
    }

    public static function getModelLabel(): string
    {
        return __('Changelog entry');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label(__('Title'))->required()->maxLength(255),
            Select::make('category')->label(__('Type'))->options(['new' => __('New'), 'fix' => __('Fix'), 'event' => __('Event')])->default('new')->required(),
            Textarea::make('body')->label(__('Details'))->rows(5)->required(),
            Toggle::make('published')->label(__('Published'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('title')->label(__('Title'))->searchable()->limit(60),
                TextColumn::make('category')->label(__('Type'))->badge(),
                TextColumn::make('published_at')->label(__('Posted'))->dateTime()->sortable(),
                IconColumn::make('published')->label(__('Published'))->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageChangelogEntries::route('/')];
    }
}
