<?php

namespace App\Filament\Resources\Hotel\Social;

use App\Emulator\Contracts\ScoreboardRepository;
use App\Emulator\Data\Scoreboard;
use App\Filament\Resources\Hotel\Social\Pages\ManageGameboards;
use App\Models\Social\Gameboard;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/**
 * Links highscore furni in rooms to the Gameboards page.
 */
class GameboardResource extends Resource
{
    protected static ?string $model = Gameboard::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotel';

    protected static ?string $slug = 'hotel/gameboards';

    protected static ?int $navigationSort = -3;

    public static function getNavigationLabel(): string
    {
        return __('Gameboards');
    }

    public static function getModelLabel(): string
    {
        return __('gameboard');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('Name'))->required()->maxLength(80),
            Select::make('item_id')
                ->label(__('Scoreboard'))
                ->helperText(__('Highscore furni placed in a room. Place one in-game first.'))
                ->options(fn (): array => app(ScoreboardRepository::class)->scoreboards()->mapWithKeys(fn (Scoreboard $board): array => [
                    $board->itemId => "{$board->roomName} - {$board->itemName} (#{$board->itemId})",
                ])->all())
                ->searchable()
                ->required(),
            TextInput::make('description')->label(__('Description'))->maxLength(255)->columnSpanFull(),
            TextInput::make('order_num')->label(__('Order'))->numeric()->default(0),
            Toggle::make('enabled')->label(__('Show on the website'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order_num')
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable(),
                TextColumn::make('item_id')->label(__('Scoreboard'))
                    ->formatStateUsing(fn (int $state): string => ($board = app(ScoreboardRepository::class)->find($state)) ? "{$board->roomName} (#{$state})" : __('Missing (#:id)', ['id' => $state])),
                TextColumn::make('order_num')->label(__('Order'))->sortable(),
                ToggleColumn::make('enabled')->label(__('Shown')),
            ])
            ->recordActions([EditAction::make()->mutateDataUsing(static::withRoom(...)), DeleteAction::make()]);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @return array<string, mixed>
     */
    public static function withRoom(array $data): array
    {
        $data['room_id'] = app(ScoreboardRepository::class)->find((int) ($data['item_id'] ?? 0))?->roomId;

        return $data;
    }

    public static function getPages(): array
    {
        return ['index' => ManageGameboards::route('/')];
    }
}
