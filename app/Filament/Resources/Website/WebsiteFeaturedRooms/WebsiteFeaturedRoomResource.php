<?php

namespace App\Filament\Resources\Website\WebsiteFeaturedRooms;

use App\Filament\Resources\Website\WebsiteFeaturedRooms\Pages\ManageWebsiteFeaturedRooms;
use App\Models\FeaturedRoom;
use App\Models\HotelRoom;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebsiteFeaturedRoomResource extends Resource
{
    protected static ?string $model = FeaturedRoom::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/featured-rooms';

    public static function getNavigationLabel(): string
    {
        return __('Featured rooms');
    }

    public static function getModelLabel(): string
    {
        return __('Featured room');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('room_id')
                ->label(__('Room'))
                ->searchable()
                ->getSearchResultsUsing(fn (string $search): array => HotelRoom::query()->where('caption', 'like', '%' . addcslashes($search, '%_\\') . '%')->limit(30)->pluck('caption', 'id')->all())
                ->getOptionLabelUsing(fn ($value): ?string => HotelRoom::query()->find($value)?->caption)
                ->required(),
            TextInput::make('note')->label(__('Note'))->maxLength(255)->placeholder(__('Why we like it')),
            TextInput::make('position')->label(__('Order'))->numeric()->default(0),
            Toggle::make('room_of_the_week')->label(__('Room of the week'))->helperText(__('Only one room can be the room of the week.')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('room'))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('room.caption')->label(__('Room'))->placeholder('-'),
                TextColumn::make('note')->label(__('Note'))->limit(50),
                IconColumn::make('room_of_the_week')->label(__('Room of the week'))->boolean(),
                TextColumn::make('position')->label(__('Order'))->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteFeaturedRooms::route('/')];
    }
}
