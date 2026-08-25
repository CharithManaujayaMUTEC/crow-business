<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    protected static ?string $navigationLabel = 'Payroll';
    protected static ?string $pluralModelLabel = 'Payroll';
    protected static ?string $modelLabel = 'Payroll';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')->relationship('employee','name')->searchable()->preload()->required(),
TextInput::make('period')->required(),
DatePicker::make('payment_date'),
TextInput::make('basic_salary')->numeric()->required(),
TextInput::make('allowance')->numeric()->default(0),
TextInput::make('deduction')->numeric()->default(0),
TextInput::make('net_salary')->numeric()->required(),
Select::make('status')->options(['pending'=>'Pending','paid'=>'Paid'])->default('pending'),
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
TextColumn::make('employee.name')->searchable(),
TextColumn::make('period')->sortable(),
TextColumn::make('net_salary')->money('LKR'),
TextColumn::make('status')->badge()
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
            'index' => Pages\ListPayroll::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
