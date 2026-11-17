<?php

namespace App\Filament\Resources\User\Referrals;

use App\Filament\Resources\User\Referrals\Pages\ListReferrals;
use App\Models\User\Referral;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static ?string $slug = 'user/referrals';

    public static function getNavigationLabel(): string
    {
        return __('Referrals');
    }

    public static function getModelLabel(): string
    {
        return __('Referral');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable(),
                TextColumn::make('user_id')->label(__('Inviter ID'))->searchable()->sortable(),
                TextColumn::make('referred_user_id')->label(__('Invited ID'))->searchable()->sortable(),
                TextColumn::make('referred_user_ip')->label(__('Invited IP'))->searchable(),
                TextColumn::make('created_at')->label(__('Date'))->dateTime()->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => ListReferrals::route('/')];
    }
}
