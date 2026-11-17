<?php

namespace App\Filament\Resources\Hotel\Rooms;

use App\Filament\Resources\Hotel\Rooms\Pages\ManageRooms;
use App\Models\HotelRoom;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model = HotelRoom::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotel';

    protected static ?string $slug = 'hotel/rooms';

    protected static ?string $recordTitleAttribute = 'caption';

    public static function getNavigationLabel(): string
    {
        return __('Rooms');
    }

    public static function getModelLabel(): string
    {
        return __('Room');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('caption')->label(__('Room name'))->required()->maxLength(100),
            Textarea::make('description')->label(__('Description'))->maxLength(255)->columnSpanFull(),
            Select::make('state')->label(__('Door'))->native(false)->options([
                'open' => __('Open'),
                'locked' => __('Doorbell'),
                'password' => __('Password'),
                'invisible' => __('Invisible'),
            ])->required(),
            TextInput::make('users_max')->label(__('Max visitors'))->numeric()->minValue(1)->maxValue(250)->required(),
            TextInput::make('tags')->label(__('Tags'))->maxLength(100),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('users_now', 'desc')
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable(),
                TextColumn::make('caption')->label(__('Room name'))->searchable()->limit(32),
                TextColumn::make('ownerUser.username')->label(__('Owner'))->searchable(),
                TextColumn::make('users_now')->label(__('Inside'))->formatStateUsing(fn ($state, HotelRoom $record) => $state . ' / ' . $record->users_max)->sortable(),
                TextColumn::make('score')->label(__('Likes'))->sortable(),
                TextColumn::make('state')->label(__('Door'))->badge(),
                TextColumn::make('model_name')->label(__('Layout'))->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('state')->options(['open' => 'Open', 'locked' => 'Doorbell', 'password' => 'Password', 'invisible' => 'Invisible']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRooms::route('/')];
    }
}
