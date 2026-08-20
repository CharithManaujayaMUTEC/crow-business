<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Resources\Pages\ListRecords;

class ListExpense extends ListRecords
{
    protected static string $resource = ExpenseResource::class;
}
