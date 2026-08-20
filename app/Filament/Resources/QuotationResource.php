<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Quotation;
use App\Services\PdfDocumentService;
use App\Services\QuotationService;
use Filament\Actions\Action;
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

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->relationship('customer', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Hidden::make('number'),

            Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'accepted' => 'Accepted',
                    'rejected' => 'Rejected',
                    'expired' => 'Expired',
                    'converted' => 'Converted',
                ])
                ->default('draft')
                ->required(),

            DatePicker::make('issued_at')->default(now())->required(),
            DatePicker::make('valid_until'),

            TextInput::make('subtotal')->numeric()->default(0),
            TextInput::make('discount')->numeric()->default(0),
            TextInput::make('tax')->numeric()->default(0),
            TextInput::make('total')->numeric()->default(0)->required(),

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
                TextColumn::make('issued_at')->date(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Quotation $record) {
                        $record->load([
                            'customer',
                            'items',
                        ]);

                        return response()->streamDownload(
                            function () use ($record) {
                                echo \Barryvdh\DomPDF\Facade\Pdf::loadView(
                                    'pdf.quotation',
                                    ['quotation' => $record]
                                )->output();
                            },
                            $record->number . '.pdf'
                        );
                    }),

                Action::make('download')
                    ->label('Open PDF')
                    ->url(fn (Quotation $record) =>
                        $record->pdf_path
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->pdf_path)
                            : null
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (Quotation $record) => filled($record->pdf_path)),

                Action::make('accept')
                    ->label('Accept')
                    ->color('success')
                    ->visible(fn (Quotation $record) =>
                        in_array($record->status, ['draft', 'sent'], true)
                    )
                    ->requiresConfirmation()
                    ->action(function (Quotation $record) {
                        $record->update(['status' => 'accepted']);

                        Notification::make()
                            ->title('Quotation accepted')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->visible(fn (Quotation $record) =>
                        in_array($record->status, ['draft', 'sent'], true)
                    )
                    ->requiresConfirmation()
                    ->action(function (Quotation $record) {
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->title('Quotation rejected')
                            ->warning()
                            ->send();
                    }),

                Action::make('convert')
                    ->label('Convert to Invoice')
                    ->color('primary')
                    ->visible(fn (Quotation $record) =>
                        $record->status === 'accepted' && ! $record->invoice
                    )
                    ->requiresConfirmation()
                    ->action(function (Quotation $record) {
                        $invoice = app(QuotationService::class)->convertToInvoice($record);

                        Notification::make()
                            ->title("Invoice {$invoice->number} created")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotation::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
