<?php

namespace App\Filament\Resources\Website\WebsitePolls;

use App\Filament\Resources\Website\WebsitePolls\Pages\ManageWebsitePolls;
use App\Models\WebsitePoll;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebsitePollResource extends Resource
{
    protected static ?string $model = WebsitePoll::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/polls';

    public static function getNavigationLabel(): string
    {
        return __('Polls');
    }

    public static function getModelLabel(): string
    {
        return __('Poll');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('question')->label(__('Question'))->required()->maxLength(255),
            Textarea::make('options')->label(__('Answers'))->helperText(__('One answer per line. Changing them after people vote can skew the results.'))->rows(5)->required(),
            DateTimePicker::make('closes_at')->label(__('Closes at'))->helperText(__('Leave empty to keep it open.')),
            Toggle::make('published')->label(__('Published'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('question')->label(__('Question'))->searchable()->limit(60),
                TextColumn::make('votes_count')->counts('votes')->label(__('Votes')),
                TextColumn::make('closes_at')->label(__('Closes'))->dateTime()->placeholder(__('Never')),
                IconColumn::make('published')->label(__('Published'))->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsitePolls::route('/')];
    }
}
