<?php

namespace App\Filament\Resources\Website\HallOfFameEntries;

use App\Filament\Resources\Website\HallOfFameEntries\Pages\ManageHallOfFameEntries;
use App\Models\HallOfFameEntry;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HallOfFameEntryResource extends Resource
{
    protected static ?string $model = HallOfFameEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/hall-of-fame';

    public static function getNavigationLabel(): string
    {
        return __('Hall of fame');
    }

    public static function getModelLabel(): string
    {
        return __('Hall of fame entry');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label(__('Player'))
                ->searchable()
                ->getSearchResultsUsing(fn (string $search): array => User::query()->where('username', 'like', addcslashes($search, '%_\\') . '%')->limit(30)->pluck('username', 'id')->all())
                ->getOptionLabelUsing(fn ($value): ?string => User::query()->find($value)?->username)
                ->required(),
            TextInput::make('title')->label(__('Title'))->placeholder(__('Builder of the week'))->required()->maxLength(80),
            TextInput::make('note')->label(__('Why'))->maxLength(255),
            DatePicker::make('awarded_on')->label(__('Date'))->default(today())->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user:id,username'))
            ->defaultSort('awarded_on', 'desc')
            ->columns([
                TextColumn::make('user.username')->label(__('Player'))->placeholder('-'),
                TextColumn::make('title')->label(__('Title'))->searchable(),
                TextColumn::make('note')->label(__('Why'))->limit(60),
                TextColumn::make('awarded_on')->label(__('Date'))->date()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageHallOfFameEntries::route('/')];
    }
}
