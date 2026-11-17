<?php

namespace App\Filament\Resources\Website\WebsiteRuleCategories;

use App\Filament\Resources\Website\WebsiteRuleCategories\Pages\ManageWebsiteRuleCategories;
use App\Models\Help\WebsiteRuleCategory;
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

class WebsiteRuleCategoryResource extends Resource
{
    protected static ?string $model = WebsiteRuleCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/rule-categories';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Rule categories');
    }

    public static function getModelLabel(): string
    {
        return __('Rule category');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('Name'))->maxLength(255)->required(),
            Textarea::make('description')->label(__('Description'))->rows(4)->columnSpanFull(),
            TextInput::make('badge')->label(__('Badge code'))->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('description')->label(__('Description'))->searchable()->sortable()->limit(60),
                TextColumn::make('badge')->label(__('Badge'))->searchable()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteRuleCategories::route('/')];
    }
}
