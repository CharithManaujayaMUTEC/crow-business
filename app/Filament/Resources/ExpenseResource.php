<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationLabel = 'Expenses';
    protected static ?string $pluralModelLabel = 'Expenses';
    protected static ?string $modelLabel = 'Expense';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('expense_date')->default(now())->required(),
TextInput::make('category')->required(),
TextInput::make('supplier'),
Textarea::make('description')->columnSpanFull(),
TextInput::make('amount')->numeric()->prefix('LKR')->required(),
Select::make('payment_method')->options(['cash'=>'Cash','bank'=>'Bank Transfer','card'=>'Card']),
TextInput::make('reference'),
FileUpload::make('invoice_path')->directory('expenses/invoices')->disk('public'),
FileUpload::make('quotation_path')->directory('expenses/quotations')->disk('public'),
Textarea::make('notes')->columnSpanFull()
])
->columns([
    'default' => 1,
    'xl' => 2,
]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
TextColumn::make('expense_date')->date()->sortable(),
TextColumn::make('category')->searchable(),
TextColumn::make('supplier')->searchable(),
TextColumn::make('amount')->money('LKR')
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpense::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
