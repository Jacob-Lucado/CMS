<?php

namespace App\Filament\Resources\Security\WebsiteBetaCodes;

use App\Filament\Resources\Security\WebsiteBetaCodes\Pages\ManageWebsiteBetaCodes;
use App\Models\Miscellaneous\WebsiteBetaCode;
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

class WebsiteBetaCodeResource extends Resource
{
    protected static ?string $model = WebsiteBetaCode::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|\UnitEnum|null $navigationGroup = 'Security';

    protected static ?string $slug = 'security/beta-codes';

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationLabel(): string
    {
        return __('Beta codes');
    }

    public static function getModelLabel(): string
    {
        return __('Beta code');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label(__('Code'))->maxLength(255)->required()->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('code')->label(__('Code'))->searchable()->sortable()->copyable(),
                TextColumn::make('user.username')->label(__('Used by'))->searchable()->sortable(),
                TextColumn::make('created_at')->label(__('Created'))->searchable()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteBetaCodes::route('/')];
    }
}
