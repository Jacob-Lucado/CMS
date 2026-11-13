<?php

namespace App\Filament\Resources\User\DiscordAccounts;

use App\Filament\Resources\User\DiscordAccounts\Pages\ManageDiscordAccounts;
use App\Filament\Resources\User\Users\UserResource;
use App\Filament\Tables\Columns\UserAvatarColumn;
use App\Models\User\WebsiteDiscordAccount;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Every player who linked Discord: find a player by Discord name or ID, jump to them, or unlink.
 */
class DiscordAccountResource extends Resource
{
    protected static ?string $model = WebsiteDiscordAccount::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static ?string $slug = 'user-management/discord';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Discord links');
    }

    public static function getModelLabel(): string
    {
        return __('Discord link');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Discord links');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->columns([
                UserAvatarColumn::make('avatar')
                    ->pointer('user.look')
                    ->label('')
                    ->options('&size=m&head_direction=3&gesture=sml&headonly=1'),
                TextColumn::make('user.username')
                    ->label(__('Player'))
                    ->weight('bold')
                    ->searchable(),
                ImageColumn::make('discord_avatar')
                    ->label('')
                    ->state(fn (WebsiteDiscordAccount $record): ?string => $record->avatarUrl())
                    ->circular()
                    ->imageHeight(28),
                TextColumn::make('discord_username')
                    ->label(__('Discord name'))
                    ->searchable(),
                TextColumn::make('discord_id')
                    ->label(__('Discord ID'))
                    ->copyable()
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Linked'))
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('player')
                    ->label(__('Open player'))
                    ->icon('heroicon-o-user')
                    ->color('gray')
                    ->url(fn (WebsiteDiscordAccount $record): ?string => $record->user ? UserResource::getUrl('edit', ['record' => $record->user]) : null),
                DeleteAction::make()
                    ->label(__('Unlink'))
                    ->icon('heroicon-o-link-slash')
                    ->modalHeading(__('Unlink this Discord account?'))
                    ->modalDescription(__('The player can still sign in with their password, and can link Discord again from their profile.'))
                    ->successNotificationTitle(__('Discord unlinked')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDiscordAccounts::route('/'),
        ];
    }
}
