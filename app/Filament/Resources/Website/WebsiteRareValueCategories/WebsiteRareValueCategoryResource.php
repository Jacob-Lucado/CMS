<?php

namespace App\Filament\Resources\Website\WebsiteRareValueCategories;

use App\Filament\Resources\Website\WebsiteRareValueCategories\Pages\ManageWebsiteRareValueCategories;
use App\Models\Community\RareValue\WebsiteRareValueCategory;
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

class WebsiteRareValueCategoryResource extends Resource
{
    protected static ?string $model = WebsiteRareValueCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/rare-categories';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Rare categories');
    }

    public static function getModelLabel(): string
    {
        return __('Rare category');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('Name'))->maxLength(255)->required(),
            TextInput::make('badge')->label(__('Badge code'))->maxLength(255),
            TextInput::make('priority')->label(__('Priority'))->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'asc')
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('priority')->label(__('Priority'))->searchable()->sortable(),
                TextColumn::make('badge')->label(__('Badge'))->searchable()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteRareValueCategories::route('/')];
    }
}
