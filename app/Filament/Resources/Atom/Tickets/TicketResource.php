<?php

namespace App\Filament\Resources\Atom\Tickets;

use App\Filament\Resources\Atom\Tickets\Pages\ListTickets;
use App\Models\Help\WebsiteHelpCenterTicket;
use App\Services\Help\TicketService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TicketResource extends Resource
{
    protected static ?string $model = WebsiteHelpCenterTicket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lifebuoy';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $slug = 'website/tickets';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return __('Support tickets');
    }

    public static function getModelLabel(): string
    {
        return __('Ticket');
    }

    public static function getNavigationBadge(): ?string
    {
        $open = WebsiteHelpCenterTicket::query()->where('open', 1)->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'category'])->withCount('replies');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('user.username')->label(__('Player')),
            TextEntry::make('category.name')->label(__('Category'))->placeholder('-'),
            TextEntry::make('title')->label(__('Subject'))->columnSpanFull(),
            TextEntry::make('content')->label(__('Message'))->html()->columnSpanFull(),
            TextEntry::make('conversation')
                ->label(__('Replies'))
                ->state(fn (WebsiteHelpCenterTicket $record): string => $record->replies()->with('user')->oldest('id')->get()
                    ->map(fn ($reply) => '<p><strong>' . e($reply->user?->username ?? '?') . '</strong> (' . e((string) $reply->created_at) . ')</p>' . $reply->content)
                    ->implode('<hr>') ?: '-')
                ->html()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable(),
                TextColumn::make('title')->label(__('Subject'))->searchable()->limit(50),
                TextColumn::make('user.username')->label(__('Player'))->searchable(),
                TextColumn::make('category.name')->label(__('Category'))->toggleable(),
                TextColumn::make('replies_count')->label(__('Replies'))->sortable(),
                IconColumn::make('open')->label(__('Open'))->boolean(),
                TextColumn::make('created_at')->label(__('Opened'))->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('open')->label(__('Status'))->trueLabel(__('Open'))->falseLabel(__('Closed'))->default(true),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('reply')
                    ->label(__('Reply'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([Textarea::make('content')->label(__('Your reply'))->required()->rows(5)])
                    ->action(function (WebsiteHelpCenterTicket $record, array $data): void {
                        app(TicketService::class)->reply(Auth::user(), $record, nl2br(e($data['content'])));
                        Notification::make()->title(__('Reply sent'))->success()->send();
                    }),
                Action::make('toggle')
                    ->label(fn (WebsiteHelpCenterTicket $record) => $record->open ? __('Close') : __('Reopen'))
                    ->icon(fn (WebsiteHelpCenterTicket $record) => $record->open ? 'heroicon-o-check-circle' : 'heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(fn (WebsiteHelpCenterTicket $record) => app(TicketService::class)->toggle(Auth::user(), $record)),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTickets::route('/')];
    }
}
