<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RecurringService;
use App\Models\Service;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment Details')
                ->schema([
                    Select::make('invoice_id')
                        ->relationship('invoice', 'number')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    DatePicker::make('paid_at')
                        ->default(now())
                        ->required(),

                    Select::make('method')
                        ->options([
                            'cash' => 'Cash',
                            'bank' => 'Bank Transfer',
                            'card' => 'Card',
                            'online' => 'Online',
                        ]),

                    TextInput::make('reference'),

                    TextInput::make('amount')
                        ->numeric()
                        ->prefix('LKR')
                        ->required(),

                    Textarea::make('notes')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Payment Items')
                ->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Select::make('item_type')
                                ->label('Type')
                                ->options([
                                    'product' => 'Product',
                                    'service' => 'Service',
                                    'recurring_service' => 'Recurring Service',
                                    'custom' => 'Custom',
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('item_id', null);
                                    $set('description', null);
                                    $set('unit_price', 0);
                                    $set('tax_rate', 0);
                                    $set('total', 0);
                                }),

                            Select::make('item_id')
                                ->label('Item')
                                ->options(function (Get $get): array {
                                    return match ($get('item_type')) {
                                        'product' => Product::query()
                                            ->where('is_active', true)
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->all(),

                                        'service' => Service::query()
                                            ->where('is_active', true)
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->all(),

                                        'recurring_service' => RecurringService::query()
                                            ->with('service')
                                            ->where('status', 'active')
                                            ->get()
                                            ->mapWithKeys(fn ($record) => [
                                                $record->id => $record->service->name
                                                    . ' - '
                                                    . ucfirst($record->frequency),
                                            ])
                                            ->all(),

                                        default => [],
                                    };
                                })
                                ->searchable()
                                ->preload()
                                ->visible(fn (Get $get) => $get('item_type') !== 'custom')
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, $state): void {
                                    $type = $get('item_type');

                                    if (! $state || $type === 'custom') {
                                        return;
                                    }

                                    [$description, $price, $taxRate] = match ($type) {
                                        'product' => (function () use ($state) {
                                            $record = Product::find($state);

                                            return [
                                                $record?->description,
                                                $record?->price ?? 0,
                                                $record?->tax_rate ?? 0,
                                            ];
                                        })(),

                                        'service' => (function () use ($state) {
                                            $record = Service::find($state);

                                            return [
                                                $record?->description,
                                                $record?->price ?? 0,
                                                $record?->tax_rate ?? 0,
                                            ];
                                        })(),

                                        'recurring_service' => (function () use ($state) {
                                            $record = RecurringService::with('service')->find($state);

                                            return [
                                                $record?->service?->description,
                                                $record?->amount ?? 0,
                                                $record?->service?->tax_rate ?? 0,
                                            ];
                                        })(),

                                        default => [null, 0, 0],
                                    };

                                    $set('description', $description);
                                    $set('unit_price', $price);
                                    $set('tax_rate', $taxRate);

                                    $quantity = (float) ($get('quantity') ?? 1);

                                    $set(
                                        'total',
                                        round($quantity * (float) $price, 2)
                                    );
                                }),

                            Textarea::make('description')
                                ->label('Item Description')
                                ->rows(2)
                                ->columnSpanFull(),

                            TextInput::make('quantity')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, $state): void {
                                    $set(
                                        'total',
                                        round(
                                            (float) $state * (float) ($get('unit_price') ?? 0),
                                            2
                                        )
                                    );
                                }),

                            TextInput::make('unit_price')
                                ->label('Rate')
                                ->numeric()
                                ->prefix('LKR')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, $state): void {
                                    $set(
                                        'total',
                                        round(
                                            (float) ($get('quantity') ?? 1) * (float) $state,
                                            2
                                        )
                                    );
                                }),

                            TextInput::make('tax_rate')
                                ->label('Tax %')
                                ->numeric()
                                ->default(0),

                            TextInput::make('total')
                                ->label('Amount')
                                ->numeric()
                                ->prefix('LKR')
                                ->readOnly()
                                ->dehydrated(true),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add Item')
                        ->reorderable()
                        ->collapsible()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.number')->searchable(),
                TextColumn::make('customer.name')->searchable(),
                TextColumn::make('amount')->money('LKR'),
                TextColumn::make('method')->badge(),
                TextColumn::make('paid_at')->date(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),

                \Filament\Actions\Action::make('slip')
                    ->label('Download Slip')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Payment $record) {
                        $record->load([
                            'customer',
                            'invoice',
                            'items',
                        ]);

                        return response()->streamDownload(
                            fn () => print(
                                \Barryvdh\DomPDF\Facade\Pdf::loadView(
                                    'pdf.payment-slip',
                                    ['payment' => $record]
                                )->output()
                            ),
                            'PAY-' . $record->id . '.pdf'
                        );
                    }),

                \Filament\Actions\Action::make('view_slip')
                    ->label('View PDF')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Payment $record) => route(
                        'pdf.payment',
                        $record
                    ))
                    ->openUrlInNewTab(),

                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayment::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}