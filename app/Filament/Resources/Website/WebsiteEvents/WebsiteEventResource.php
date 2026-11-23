<?php

namespace App\Filament\Resources\Website\WebsiteEvents;

use App\Filament\Resources\Website\WebsiteEvents\Pages\ManageWebsiteEvents;
use App\Models\WebsiteEvent;
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

class WebsiteEventResource extends Resource
{
    protected static ?string $model = WebsiteEvent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/events';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return __('Events');
    }

    public static function getModelLabel(): string
    {
        return __('Event');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label(__('Title'))->maxLength(255)->required(),
            Textarea::make('description')->label(__('Description'))->rows(4)->columnSpanFull(),
            TextInput::make('location')->label(__('Where'))->maxLength(255)->placeholder('Room name or link'),
            DateTimePicker::make('starts_at')->label(__('Starts'))->seconds(false)->required(),
            Toggle::make('published')->label(__('Show on the site'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('attendees'))
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('title')->label(__('Title'))->searchable()->sortable(),
                TextColumn::make('location')->label(__('Where'))->searchable()->sortable(),
                TextColumn::make('starts_at')->label(__('Starts'))->searchable()->sortable()->dateTime(),
                TextColumn::make('attendees_count')->label(__('Going'))->sortable(),
                IconColumn::make('published')->label(__('Shown'))->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebsiteEvents::route('/')];
    }
}
