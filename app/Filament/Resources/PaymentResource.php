<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Services\PdfDocumentService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
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

            TextInput::make('amount')
                ->numeric()
                ->prefix('LKR')
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
            Textarea::make('notes')->columnSpanFull(),
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
                        ]);

                        return response()->streamDownload(
                            function () use ($record) {
                                echo \Barryvdh\DomPDF\Facade\Pdf::loadView(
                                    'pdf.payment-slip',
                                    ['payment' => $record]
                                )->output();
                            },
                            'PAY-' . $record->id . '.pdf'
                        );
                    }),

                \Filament\Actions\Action::make('open_slip')
                    ->label('Open Slip')
                    ->url(fn (Payment $record) =>
                        $record->payment_slip_path
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->payment_slip_path)
                            : null
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (Payment $record) => filled($record->payment_slip_path)),

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
