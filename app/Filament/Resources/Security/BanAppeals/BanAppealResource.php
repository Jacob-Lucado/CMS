<?php

namespace App\Filament\Resources\Security\BanAppeals;

use App\Filament\Resources\Security\BanAppeals\Pages\ManageBanAppeals;
use App\Models\BanAppeal;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BanAppealResource extends Resource
{
    protected static ?string $model = BanAppeal::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static string|\UnitEnum|null $navigationGroup = 'Security';

    protected static ?string $slug = 'security/ban-appeals';

    public static function getNavigationLabel(): string
    {
        return __('Ban appeals');
    }

    public static function getModelLabel(): string
    {
        return __('Ban appeal');
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = BanAppeal::query()->where('status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function table(Table $table): Table
    {
        $decide = fn (string $status, string $label, string $color): Action => Action::make($status)
            ->label($label)
            ->color($color)
            ->visible(fn (BanAppeal $record): bool => $record->status === 'pending')
            ->schema([Textarea::make('staff_note')->label(__('Note for the file'))->rows(3)])
            ->action(function (BanAppeal $record, array $data) use ($status): void {
                $record->update([
                    'status' => $status,
                    'staff_note' => $data['staff_note'] ?? null,
                    'decided_by' => auth()->id(),
                    'decided_at' => now(),
                ]);
                Notification::make()->title(__('Appeal marked :status', ['status' => $status]))->success()->send();
            });

        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('username')->label(__('Player'))->searchable(),
                TextColumn::make('message')->label(__('Appeal'))->wrap()->limit(160)->tooltip(fn (BanAppeal $record): string => $record->message),
                TextColumn::make('status')->label(__('Status'))->badge()->color(fn (string $state): string => match ($state) {
                    'accepted' => 'success',
                    'denied' => 'danger',
                    default => 'warning',
                }),
                TextColumn::make('staff_note')->label(__('Staff note'))->limit(60)->placeholder('-'),
                TextColumn::make('created_at')->label(__('Sent'))->dateTime()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(['pending' => __('Pending'), 'accepted' => __('Accepted'), 'denied' => __('Denied')])->default('pending')])
            ->recordActions([$decide('accepted', __('Accept'), 'success'), $decide('denied', __('Deny'), 'danger'), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBanAppeals::route('/')];
    }
}
