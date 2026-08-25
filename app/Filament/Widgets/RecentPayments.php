<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentPayments extends TableWidget
{
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->with([
                        'customer',
                        'invoice',
                    ])
                    ->latest('paid_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('paid_at')
                    ->label('Date')
                    ->date(),

                TextColumn::make('customer.name')
                    ->label('Customer'),

                TextColumn::make('invoice.number')
                    ->label('Invoice'),

                TextColumn::make('method')
                    ->label('Method')
                    ->badge(),

                TextColumn::make('amount')
                    ->money('LKR'),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->placeholder('-'),
            ])
            ->paginated(false);
    }
}