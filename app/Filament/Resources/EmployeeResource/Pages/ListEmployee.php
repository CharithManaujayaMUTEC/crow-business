<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Resources\Pages\ListRecords;

class ListEmployee extends ListRecords
{
    protected static string $resource = EmployeeResource::class;
}
