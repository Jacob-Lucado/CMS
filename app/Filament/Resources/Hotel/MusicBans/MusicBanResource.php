<?php

namespace App\Filament\Resources\Hotel\MusicBans;

use App\Filament\Concerns\RequiresEmulatorDriver;
use App\Filament\Resources\Hotel\MusicBans\Pages\ManageMusicBans;
use App\Models\RoomStreamBan;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Links nobody can play with :music (PlusEMU). Deleting a ban lets the link play again.
 */
class MusicBanResource extends Resource
{
    use RequiresEmulatorDriver;

    protected static ?string $model = RoomStreamBan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-no-symbol';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotel';

    protected static ?string $slug = 'hotel/music-bans';

    protected static function requiredEmulatorDriver(): string
    {
        return 'plus';
    }

    public static function getNavigationLabel(): string
    {
        return __('Banned music');
    }

    public static function getModelLabel(): string
    {
        return __('banned link');
    }

    public static function getPluralModelLabel(): string
    {
        return __('banned links');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('url')
                ->label(__('Link'))
                ->helperText(__('A YouTube video or playlist, a SoundCloud track or playlist, or an audio stream. A link with a playlist bans the whole playlist.'))
                ->url()
                ->maxLength(500)
                ->required()
                ->rule(fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                    if (RoomStreamBan::keyFor((string) $value) === null) {
                        $fail(__('That is not a link :music can play.'));
                    }
                }),

            TextInput::make('reason')
                ->label(__('Reason'))
                ->maxLength(200),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('url')
                    ->label(__('Link'))
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => Str::limit((string) preg_replace('#^https?://(www\.)?#', '', $state), 70))
                    ->tooltip(fn (RoomStreamBan $record): string => $record->url),

                TextColumn::make('reason')
                    ->label(__('Reason'))
                    ->placeholder('-'),

                TextColumn::make('banned_by')
                    ->label(__('Banned by'))
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('Banned'))
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make()->label(__('Unban')),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMusicBans::route('/'),
        ];
    }
}
