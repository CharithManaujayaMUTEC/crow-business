<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentInvoices extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->with('customer')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('number')
                    ->label('Invoice')
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('LKR'),

                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('LKR'),

                TextColumn::make('due_at')
                    ->label('Due')
                    ->date(),
            ])
            ->paginated(false);
    }
}