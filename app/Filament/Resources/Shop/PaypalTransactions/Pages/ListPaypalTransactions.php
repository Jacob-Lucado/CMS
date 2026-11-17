<?php

namespace App\Filament\Resources\Shop\PaypalTransactions\Pages;

use App\Filament\Resources\Shop\PaypalTransactions\PaypalTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListPaypalTransactions extends ListRecords
{
    protected static string $resource = PaypalTransactionResource::class;
}
