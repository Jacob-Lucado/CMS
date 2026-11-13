<?php

namespace App\Filament\Resources\Hotel\Social;

use App\Filament\Resources\Hotel\Social\Pages\ManageSocialReports;
use App\Models\Social\SocialPost;
use App\Models\Social\SocialReport;
use App\Services\Social\Notifier;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Posts players reported on the social feed.
 */
class SocialReportResource extends Resource
{
    protected static ?string $model = SocialReport::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotel';

    protected static ?string $slug = 'hotel/social-reports';

    protected static ?int $navigationSort = -4;

    public static function getNavigationLabel(): string
    {
        return __('Social reports');
    }

    public static function getModelLabel(): string
    {
        return __('report');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Social reports');
    }

    public static function getNavigationBadge(): ?string
    {
        $open = SocialReport::query()->whereNull('resolved_at')->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'post.user'])->orderByRaw('resolved_at IS NOT NULL')->latest())
            ->columns([
                TextColumn::make('post.body')
                    ->label(__('Post'))
                    ->placeholder(fn (SocialReport $record): string => $record->post ? '(' . $record->post->type . ')' : __('Deleted'))
                    ->description(fn (SocialReport $record): string => __('by :name', ['name' => $record->post->user->username ?? '?']))
                    ->limit(70)
                    ->wrap(),
                TextColumn::make('reason')
                    ->label(__('Reason'))
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('user.username')
                    ->label(__('Reported by'))
                    ->searchable(),
                TextColumn::make('resolved_at')
                    ->label(__('Status'))
                    ->badge()
                    ->state(fn (SocialReport $record): string => $record->resolved_at ? __('Handled') : __('Open'))
                    ->color(fn (SocialReport $record): string => $record->resolved_at ? 'gray' : 'danger'),
                TextColumn::make('created_at')
                    ->label(__('Reported'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('open')
                    ->label(__('Open only'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('resolved_at'),
                        false: fn (Builder $query) => $query->whereNotNull('resolved_at'),
                    ),
            ])
            ->recordActions([
                Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn (SocialReport $record): bool => $record->post !== null && ! $record->post->trashed())
                    ->url(fn (SocialReport $record): string => route('social.index', ['post' => $record->post_id]), shouldOpenInNewTab: true),
                Action::make('remove')
                    ->label(__('Remove post'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (SocialReport $record): bool => $record->post !== null && ! $record->post->trashed())
                    ->action(function (SocialReport $record): void {
                        if ($record->post !== null) {
                            static::removePost($record->post);
                        }
                    })
                    ->successNotificationTitle(__('Post removed')),
                Action::make('dismiss')
                    ->label(__('Dismiss'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (SocialReport $record): bool => $record->resolved_at === null)
                    ->action(fn (SocialReport $record) => $record->forceFill(['resolved_by' => auth()->id(), 'resolved_at' => now()])->save())
                    ->successNotificationTitle(__('Dismissed')),
            ]);
    }

    public static function removePost(SocialPost $post): void
    {
        SocialReport::query()->where('post_id', $post->id)->whereNull('resolved_at')->update(['resolved_by' => auth()->id(), 'resolved_at' => now()]);
        app(Notifier::class)->send($post->user_id, 'post_removed', __('A staff member removed one of your posts.'));
        $post->delete();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSocialReports::route('/'),
        ];
    }
}
