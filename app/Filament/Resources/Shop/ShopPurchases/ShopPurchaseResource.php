<?php

namespace App\Filament\Resources\Shop\ShopPurchases;

use App\Filament\Resources\Shop\ShopPurchases\Pages\ListShopPurchases;
use App\Models\Shop\WebsiteShopPurchase;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShopPurchaseResource extends Resource
{
    protected static ?string $model = WebsiteShopPurchase::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop Management';

    protected static ?string $slug = 'shop/purchases';

    public static function getNavigationLabel(): string
    {
        return __('Purchases');
    }

    public static function getModelLabel(): string
    {
        return __('Purchase');
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
                TextColumn::make('user.username')->label(__('Buyer'))->searchable(),
                TextColumn::make('package.name')->label(__('Package'))->searchable(),
                TextColumn::make('giftedTo.username')->label(__('Gifted to'))->placeholder('-'),
                TextColumn::make('created_at')->label(__('Bought'))->dateTime()->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => ListShopPurchases::route('/')];
    }
}
