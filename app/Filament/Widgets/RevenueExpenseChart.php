<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueExpenseChart extends ChartWidget
{
    protected ?string $heading = 'Revenue vs Expenses';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $labels = [];
        $revenue = [];
        $expenses = [];

        for ($month = 11; $month >= 0; $month--) {
            $date = now()->subMonths($month);

            $labels[] = $date->format('M Y');

            $revenue[] = (float) Payment::query()
                ->whereBetween('paid_at', [
                    $date->copy()->startOfMonth(),
                    $date->copy()->endOfMonth(),
                ])
                ->sum('amount');

            $expenses[] = (float) Expense::query()
                ->whereBetween('expense_date', [
                    $date->copy()->startOfMonth(),
                    $date->copy()->endOfMonth(),
                ])
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenue,
                    'borderColor' => '#f5a623',
                    'backgroundColor' => 'rgba(245, 166, 35, 0.15)',
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenses,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.10)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}