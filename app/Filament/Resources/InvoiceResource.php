<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->relationship('customer', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('quotation_id')
                ->relationship('quotation', 'number')
                ->searchable()
                ->preload(),

            Hidden::make('number'),

            Select::make('status')
                ->options([
                    'issued' => 'Issued',
                    'partially_paid' => 'Partially Paid',
                    'paid' => 'Paid',
                    'overdue' => 'Overdue',
                    'cancelled' => 'Cancelled',
                ])
                ->default('issued')
                ->required(),

            DatePicker::make('issued_at')->default(now())->required(),
            DatePicker::make('due_at'),

            TextInput::make('subtotal')->numeric()->default(0),
            TextInput::make('discount')->numeric()->default(0),
            TextInput::make('tax')->numeric()->default(0),
            TextInput::make('total')->numeric()->default(0)->required(),
            TextInput::make('balance')->numeric()->default(0),

            Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('customer.name')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('total')->money('LKR'),
                TextColumn::make('balance')->money('LKR'),
                TextColumn::make('due_at')->date(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),

                \Filament\Actions\Action::make('pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Invoice $record) {
                        $record->load([
                            'customer',
                            'quotation',
                            'items',
                            'payments',
                        ]);

                        return response()->streamDownload(
                            function () use ($record) {
                                echo \Barryvdh\DomPDF\Facade\Pdf::loadView(
                                    'pdf.invoice',
                                    ['invoice' => $record]
                                )->output();
                            },
                            $record->number . '.pdf'
                        );
                    }),

                \Filament\Actions\Action::make('open_pdf')
                    ->label('Open PDF')
                    ->url(fn (Invoice $record) =>
                        $record->pdf_path
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->pdf_path)
                            : null
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (Invoice $record) => filled($record->pdf_path)),

                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoice::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
