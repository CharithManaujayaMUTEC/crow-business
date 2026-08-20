<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\RecurringService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrowStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Customers', Customer::count())->description('Active customers'),
            Stat::make('This Month Revenue', 'LKR '.number_format(Invoice::where('status','paid')->whereMonth('created_at', now()->month)->sum('total'), 2)),
            Stat::make('Outstanding', 'LKR '.number_format(Invoice::whereIn('status',['issued','partially_paid','overdue'])->sum('balance'), 2)),
            Stat::make('Expenses This Month', 'LKR '.number_format(Expense::whereMonth('expense_date', now()->month)->sum('amount'), 2)),
            Stat::make('Recurring Services', RecurringService::where('status','active')->count()),
        ];
    }
}
