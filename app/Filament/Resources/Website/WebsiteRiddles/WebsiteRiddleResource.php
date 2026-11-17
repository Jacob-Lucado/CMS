<?php

namespace App\Filament\Resources\Website\WebsiteRiddles;

use App\Filament\Resources\Website\WebsiteRiddles\Pages\ManageWebsiteRiddles;
use App\Models\WebsiteRiddle;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebsiteRiddleResource extends Resource
{
    protected static ?string $model = WebsiteRiddle::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/riddles';

    public static function getNavigationLabel(): string
    {
        return __('Riddles');
    }

    public static function getModelLabel(): string
    {
        return __('Riddle');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('question')->label(__('Riddle'))->required()->maxLength(255),
            TextInput::make('answer')->label(__('Answer'))->required()->maxLength(255)->helperText(__('Case and punctuation do not matter. Separate more than one accepted answer with a pipe, like: clock|a clock')),
            TextInput::make('hint')->label(__('Hint'))->maxLength(255),
            TextInput::make('reward_credits')->label(__('Reward in credits'))->numeric()->minValue(0)->default(25)->required(),
            DatePicker::make('active_on')->label(__('Shows on'))->default(today())->required()->helperText(__('The riddle is live for that whole day.')),
            Toggle::make('published')->label(__('Published'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('active_on', 'desc')
            ->columns([
                TextColumn::make('active_on')->label(__('Date'))->date()->sortable(),
                TextColumn::make('question')->label(__('Riddle'))->searchable()->limit(50),
                TextColumn::make('answer')->label(__('Answer'))->limit(30),
                TextColumn::make('reward_credits')->label(__('Reward')),
                TextColumn::make('solves_count')->counts('solves')->label(__('Solved by')),
                IconColumn::make('published')->label(__('Published'))->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteRiddles::route('/')];
    }
}
