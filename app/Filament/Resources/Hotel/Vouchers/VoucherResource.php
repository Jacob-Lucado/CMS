<?php

namespace App\Filament\Resources\Hotel\Vouchers;

use App\Filament\Resources\Hotel\Vouchers\Pages\ManageVouchers;
use App\Models\Voucher;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotel';

    protected static ?string $slug = 'hotel/vouchers';

    protected static ?string $recordTitleAttribute = 'voucher';

    public static function getNavigationLabel(): string
    {
        return __('Vouchers');
    }

    public static function getModelLabel(): string
    {
        return __('Voucher');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('voucher')
                ->label(__('Code'))
                ->required()
                ->maxLength(45)
                ->unique(ignoreRecord: true)
                ->default(fn () => Str::upper(Str::random(10)))
                ->helperText(__('What players type into the shop to redeem it.')),
            Select::make('type')
                ->label(__('Reward'))
                ->native(false)
                ->options(['credits' => __('Credits'), 'duckets' => __('Duckets')])
                ->default('credits')
                ->required(),
            TextInput::make('value')->label(__('Amount'))->numeric()->minValue(1)->default(100)->required(),
            TextInput::make('max_uses')->label(__('Max uses'))->numeric()->minValue(1)->default(1)->required(),
            Toggle::make('enabled')->label(__('Enabled'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('voucher')
            ->columns([
                TextColumn::make('voucher')->label(__('Code'))->searchable()->copyable(),
                TextColumn::make('type')->label(__('Reward'))->badge(),
                TextColumn::make('value')->label(__('Amount'))->sortable(),
                TextColumn::make('current_uses')->label(__('Used'))->formatStateUsing(fn ($state, Voucher $record) => $state . ' / ' . $record->max_uses)->sortable(),
                ToggleColumn::make('enabled')->label(__('Enabled')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageVouchers::route('/')];
    }
}
