<?php

namespace App\Filament\Resources\Website\WebsiteRareValues;

use App\Filament\Resources\Website\WebsiteRareValues\Pages\ManageWebsiteRareValues;
use App\Models\Community\RareValue\WebsiteRareValue;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebsiteRareValueResource extends Resource
{
    protected static ?string $model = WebsiteRareValue::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/rare-values';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Rare values');
    }

    public static function getModelLabel(): string
    {
        return __('Rare value');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category_id')->label(__('Category'))->native(false)->relationship('category', 'name')->required(),
            TextInput::make('name')->label(__('Name'))->maxLength(255)->required(),
            TextInput::make('item_id')->label(__('Furniture ID'))->numeric(),
            TextInput::make('credit_value')->label(__('Credit value'))->numeric()->default(0),
            TextInput::make('currency_value')->label(__('Other currency value'))->numeric()->default(0),
            Select::make('currency_type')->label(__('Other currency'))->native(false)->options([0 => __('Duckets'), 5 => __('Diamonds')])->default(5),
            TextInput::make('furniture_icon')->label(__('Icon file name'))->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('category.name')->label(__('Category'))->searchable()->sortable(),
                TextColumn::make('credit_value')->label(__('Credits'))->searchable()->sortable(),
                TextColumn::make('currency_value')->label(__('Other'))->searchable()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteRareValues::route('/')];
    }
}
