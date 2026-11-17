<?php

namespace App\Filament\Resources\Website\WebsiteRules;

use App\Filament\Resources\Website\WebsiteRules\Pages\ManageWebsiteRules;
use App\Models\Help\WebsiteRule;
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

class WebsiteRuleResource extends Resource
{
    protected static ?string $model = WebsiteRule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/rules';

    protected static ?string $recordTitleAttribute = 'paragraph';

    public static function getNavigationLabel(): string
    {
        return __('Rules');
    }

    public static function getModelLabel(): string
    {
        return __('Rule');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category_id')->label(__('Category'))->native(false)->relationship('category', 'name')->required(),
            TextInput::make('paragraph')->label(__('Paragraph / number'))->maxLength(255),
            Textarea::make('rule')->label(__('Rule text'))->rows(4)->columnSpanFull()->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('category_id', 'asc')
            ->columns([
                TextColumn::make('category.name')->label(__('Category'))->searchable()->sortable(),
                TextColumn::make('paragraph')->label(__('Paragraph'))->searchable()->sortable(),
                TextColumn::make('rule')->label(__('Rule'))->searchable()->sortable()->limit(80),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteRules::route('/')];
    }
}
