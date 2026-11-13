<?php

namespace App\Filament\Resources\Hotel\Radio;

use App\Filament\Resources\Hotel\Radio\Pages\ManageRadioEntries;
use App\Models\Radio\RadioEntry;
use App\Services\Radio\RadioAccess;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Everything on the radio and everything players suggested, with who runs it.
 */
class RadioEntryResource extends Resource
{
    protected static ?string $model = RadioEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-radio';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotel';

    protected static ?string $slug = 'hotel/radio';

    protected static ?int $navigationSort = -5;

    public static function getNavigationLabel(): string
    {
        return __('Radio');
    }

    public static function getModelLabel(): string
    {
        return __('radio entry');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Radio');
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = RadioEntry::query()->pending()->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'reviewer'])
                ->orderByRaw('CASE status WHEN ? THEN 0 WHEN ? THEN 1 ELSE 2 END', [RadioEntry::PENDING, RadioEntry::APPROVED])
                ->latest())
            ->columns([
                TextColumn::make('kind')
                    ->label('')
                    ->formatStateUsing(fn (string $state): string => $state === RadioEntry::STATION ? '📡' : '♪'),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->weight('bold')
                    ->description(fn (RadioEntry $record): string => $record->subtitle)
                    ->limit(50)
                    ->searchable(['title', 'subtitle']),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        RadioEntry::APPROVED => 'success',
                        RadioEntry::PENDING => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        RadioEntry::APPROVED => __('On the radio'),
                        RadioEntry::PENDING => __('Waiting'),
                        default => __('Rejected'),
                    }),
                TextColumn::make('user.username')
                    ->label(__('Added by'))
                    ->searchable(),
                TextColumn::make('reviewer.username')
                    ->label(__('Reviewed by'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Added'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        RadioEntry::PENDING => __('Waiting'),
                        RadioEntry::APPROVED => __('On the radio'),
                        RadioEntry::REJECTED => __('Rejected'),
                    ]),
                SelectFilter::make('kind')
                    ->label(__('Type'))
                    ->options([RadioEntry::STATION => __('Stations'), RadioEntry::TRACK => __('SoundCloud')]),
            ])
            ->recordActions([
                Action::make('listen')
                    ->label(__('Listen'))
                    ->icon('heroicon-o-play')
                    ->color('gray')
                    ->url(fn (RadioEntry $record): string => $record->url, shouldOpenInNewTab: true),
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (RadioEntry $record): bool => $record->status !== RadioEntry::APPROVED)
                    ->action(fn (RadioEntry $record) => static::review($record, RadioEntry::APPROVED))
                    ->successNotificationTitle(__('It\'s on the radio')),
                Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (RadioEntry $record): bool => $record->status === RadioEntry::PENDING)
                    ->action(fn (RadioEntry $record) => static::review($record, RadioEntry::REJECTED))
                    ->successNotificationTitle(__('Rejected')),
                DeleteAction::make()
                    ->label(__('Remove'))
                    ->before(function (RadioEntry $record): void {
                        $access = app(RadioAccess::class);

                        if ($access->onAir()?->is($record)) {
                            $access->putOnAir(null);
                        }
                    }),
            ]);
    }

    public static function review(RadioEntry $record, string $status): void
    {
        $record->forceFill(['status' => $status, 'reviewed_by' => auth()->id()])->save();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRadioEntries::route('/'),
        ];
    }
}
