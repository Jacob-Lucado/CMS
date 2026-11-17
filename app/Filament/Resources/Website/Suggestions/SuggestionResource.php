<?php

namespace App\Filament\Resources\Website\Suggestions;

use App\Filament\Resources\Website\Suggestions\Pages\ManageSuggestions;
use App\Models\Suggestion;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SuggestionResource extends Resource
{
    protected static ?string $model = Suggestion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/suggestions';

    public static function getNavigationLabel(): string
    {
        return __('Suggestions');
    }

    public static function getModelLabel(): string
    {
        return __('Suggestion');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('idea')->label(__('Idea'))->content(fn (?Suggestion $record): string => $record ? $record->title . "\n\n" . $record->body : ''),
            Select::make('status')->label(__('Status'))->options(['open' => __('Open'), 'planned' => __('Planned'), 'done' => __('Done'), 'declined' => __('Declined')])->required(),
            Textarea::make('staff_reply')->label(__('Reply shown to players'))->rows(3)->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('author:id,username')->withCount('votes'))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('title')->label(__('Idea'))->searchable()->limit(50),
                TextColumn::make('author.username')->label(__('Author'))->placeholder('-'),
                TextColumn::make('votes_count')->label(__('Votes'))->sortable(),
                TextColumn::make('status')->label(__('Status'))->badge()->color(fn (string $state): string => match ($state) {
                    'done' => 'success',
                    'planned' => 'info',
                    'declined' => 'danger',
                    default => 'gray',
                }),
                TextColumn::make('created_at')->label(__('Posted'))->dateTime()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(['open' => __('Open'), 'planned' => __('Planned'), 'done' => __('Done'), 'declined' => __('Declined')])])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSuggestions::route('/')];
    }
}
