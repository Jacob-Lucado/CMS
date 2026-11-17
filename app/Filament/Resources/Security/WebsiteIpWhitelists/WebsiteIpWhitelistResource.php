<?php

namespace App\Filament\Resources\Security\WebsiteIpWhitelists;

use App\Filament\Resources\Security\WebsiteIpWhitelists\Pages\ManageWebsiteIpWhitelists;
use App\Models\Miscellaneous\WebsiteIpWhitelist;
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

class WebsiteIpWhitelistResource extends Resource
{
    protected static ?string $model = WebsiteIpWhitelist::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|\UnitEnum|null $navigationGroup = 'Security';

    protected static ?string $slug = 'security/ip-whitelist';

    protected static ?string $recordTitleAttribute = 'ip_address';

    public static function getNavigationLabel(): string
    {
        return __('IP whitelist');
    }

    public static function getModelLabel(): string
    {
        return __('Allowed address');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('ip_address')->label(__('IP address'))->maxLength(255)->required(),
            TextInput::make('asn')->label(__('Network name (ASN)'))->maxLength(255),
            Toggle::make('whitelist_asn')->label(__('Allow the whole network')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('ip_address')->label(__('IP address'))->searchable()->sortable(),
                TextColumn::make('asn')->label(__('ASN'))->searchable()->sortable(),
                IconColumn::make('whitelist_asn')->label(__('Whole network'))->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteIpWhitelists::route('/')];
    }
}
