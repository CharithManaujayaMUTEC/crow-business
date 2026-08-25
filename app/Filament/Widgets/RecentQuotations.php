<?php

namespace App\Filament\Widgets;

use App\Models\Quotation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentQuotations extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Quotation::query()
                    ->with('customer')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('number')
                    ->label('Quotation'),

                TextColumn::make('customer.name')
                    ->label('Customer'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('total')
                    ->money('LKR'),

                TextColumn::make('issued_at')
                    ->label('Issued')
                    ->date(),

                TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date(),
            ])
            ->paginated(false);
    }
}