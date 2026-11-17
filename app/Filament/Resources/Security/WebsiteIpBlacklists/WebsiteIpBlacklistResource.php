<?php

namespace App\Filament\Resources\Security\WebsiteIpBlacklists;

use App\Filament\Resources\Security\WebsiteIpBlacklists\Pages\ManageWebsiteIpBlacklists;
use App\Models\Miscellaneous\WebsiteIpBlacklist;
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

class WebsiteIpBlacklistResource extends Resource
{
    protected static ?string $model = WebsiteIpBlacklist::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-no-symbol';

    protected static string|\UnitEnum|null $navigationGroup = 'Security';

    protected static ?string $slug = 'security/ip-blacklist';

    protected static ?string $recordTitleAttribute = 'ip_address';

    public static function getNavigationLabel(): string
    {
        return __('IP blacklist');
    }

    public static function getModelLabel(): string
    {
        return __('Blocked address');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('ip_address')->label(__('IP address'))->maxLength(255)->required(),
            TextInput::make('asn')->label(__('Network name (ASN)'))->maxLength(255),
            Toggle::make('blacklist_asn')->label(__('Block the whole network')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('ip_address')->label(__('IP address'))->searchable()->sortable(),
                TextColumn::make('asn')->label(__('ASN'))->searchable()->sortable(),
                IconColumn::make('blacklist_asn')->label(__('Whole network'))->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteIpBlacklists::route('/')];
    }
}
