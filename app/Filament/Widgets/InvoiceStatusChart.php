<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class InvoiceStatusChart extends ChartWidget
{
    protected ?string $heading = 'Invoice Status';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $statuses = [
            'issued' => 'Issued',
            'partially_paid' => 'Partially Paid',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
        ];

        $data = [];

        foreach (array_keys($statuses) as $status) {
            $data[] = Invoice::query()
                ->where('status', $status)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Invoices',
                    'data' => $data,
                    'backgroundColor' => [
                        '#f5a623',
                        '#3b82f6',
                        '#22c55e',
                        '#ef4444',
                        '#6b7280',
                    ],
                ],
            ],
            'labels' => array_values($statuses),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}