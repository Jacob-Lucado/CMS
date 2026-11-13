<?php

namespace App\Filament\Resources\Hotel\Social;

use App\Contracts\Rcon;
use App\Emulator\Emulator;
use App\Filament\Resources\Hotel\Social\Pages\ManagePublicRooms;
use App\Models\Game\PlusNavigatorPublic;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Picks which rooms show in the Public tab of the in-game navigator (PlusEMU).
 */
class PublicRoomResource extends Resource
{
    protected static ?string $model = PlusNavigatorPublic::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotel';

    protected static ?string $slug = 'hotel/public-rooms';

    protected static ?int $navigationSort = -2;

    public static function canAccess(): bool
    {
        return Emulator::driver() === 'plus' && parent::canAccess();
    }

    public static function getNavigationLabel(): string
    {
        return __('Public rooms');
    }

    public static function getModelLabel(): string
    {
        return __('public room');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('room_id')
                ->label(__('Room'))
                ->searchable()
                ->getSearchResultsUsing(fn (string $search): array => DB::table('rooms')->where('caption', 'like', '%' . $search . '%')->orWhere('id', $search)->orderBy('caption')->limit(30)->pluck('caption', 'id')->map(fn (string $caption, int $id): string => "{$caption} (#{$id})")->all())
                ->getOptionLabelUsing(fn (int|string $value): string => (string) DB::table('rooms')->where('id', $value)->value('caption') . " (#{$value})")
                ->live()
                ->afterStateUpdated(function (Set $set, int|string|null $state): void {
                    $room = $state === null ? null : DB::table('rooms')->where('id', $state)->first(['caption', 'description']);
                    if ($room !== null) {
                        $set('caption', (string) $room->caption);
                        $set('description', (string) $room->description);
                    }
                })
                ->unique(ignoreRecord: true)
                ->disabledOn('edit')
                ->required(),
            TextInput::make('caption')->label(__('Name in the navigator'))->required()->maxLength(64),
            TextInput::make('description')->label(__('Description'))->maxLength(150)->dehydrateStateUsing(fn (?string $state): string => (string) $state),
            TextInput::make('image_url')->label(__('Picture link (optional)'))->maxLength(500)->dehydrateStateUsing(fn (?string $state): string => (string) $state),
            TextInput::make('order_num')->label(__('Order'))->numeric()->default(1),
            Toggle::make('enabled')->label(__('Listed'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order_num')
            ->columns([
                TextColumn::make('caption')->label(__('Name'))->searchable(),
                TextColumn::make('room_id')->label(__('Room'))->prefix('#'),
                TextColumn::make('order_num')->label(__('Order'))->sortable(),
                ToggleColumn::make('enabled')->label(__('Listed'))->afterStateUpdated(fn () => static::reloadNavigator()),
            ])
            ->recordActions([
                EditAction::make()->after(fn () => static::reloadNavigator()),
                DeleteAction::make()->after(fn () => static::reloadNavigator()),
            ]);
    }

    /** The game server reads this list at startup, so ask it to read it again. */
    public static function reloadNavigator(): void
    {
        try {
            app(Rcon::class)->sendCommand('reload_navigator', []);
        } catch (Throwable) {
            // The list still applies the next time the hotel starts.
        }
    }

    public static function getPages(): array
    {
        return ['index' => ManagePublicRooms::route('/')];
    }
}
