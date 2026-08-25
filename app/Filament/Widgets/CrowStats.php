<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\RecurringService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrowStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $thisMonth = now()->month;
        $thisYear = now()->year;

        $revenueThisMonth = Payment::query()
            ->whereMonth('paid_at', $thisMonth)
            ->whereYear('paid_at', $thisYear)
            ->sum('amount');

        $expensesThisMonth = Expense::query()
            ->whereMonth('expense_date', $thisMonth)
            ->whereYear('expense_date', $thisYear)
            ->sum('amount');

        $outstanding = Invoice::query()
            ->whereIn('status', [
                'issued',
                'partially_paid',
                'overdue',
            ])
            ->sum('balance');

        $overdueInvoices = Invoice::query()
            ->where('status', 'overdue')
            ->count();

        $pendingQuotations = Quotation::query()
            ->whereIn('status', [
                'draft',
                'sent',
            ])
            ->count();

        return [
            Stat::make(
                'Customers',
                Customer::count()
            )
                ->description('Total customers')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make(
                'Revenue This Month',
                'LKR ' . number_format($revenueThisMonth, 2)
            )
                ->description('Payments received this month')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make(
                'Outstanding',
                'LKR ' . number_format($outstanding, 2)
            )
                ->description($overdueInvoices . ' overdue invoice(s)')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make(
                'Expenses This Month',
                'LKR ' . number_format($expensesThisMonth, 2)
            )
                ->description('Recorded expenses this month')
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger'),

            Stat::make(
                'Pending Quotations',
                $pendingQuotations
            )
                ->description('Draft and sent quotations')
                ->icon('heroicon-o-document-text')
                ->color('info'),

            Stat::make(
                'Employees',
                Employee::count()
            )
                ->description('Employee profiles')
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make(
                'Recurring Services',
                RecurringService::query()
                    ->where('status', 'active')
                    ->count()
            )
                ->description('Active recurring services')
                ->icon('heroicon-o-arrow-path')
                ->color('success'),
        ];
    }
}