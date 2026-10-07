<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Employee;
use App\Models\Payroll;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use UnitEnum;

class PayrollResource extends Resource
{
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $model = Payroll::class;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Payroll';

    protected static ?string $modelLabel = 'Payroll';

    protected static ?string $pluralModelLabel = 'Payroll';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('period')
                    ->label('Payroll Period')
                    ->placeholder('2026-10')
                    ->required()
                    ->maxLength(20),

                DatePicker::make('payment_date')
                    ->label('Payment Date'),

                TextInput::make('basic_salary')
                    ->numeric()
                    ->prefix('LKR')
                    ->required()
                    ->default(0),

                TextInput::make('allowance')
                    ->label('Allowance')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('fixed_allowance')
                    ->label('Fixed Allowance')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('other_allowances')
                    ->label('Other Allowances')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('overtime_amount')
                    ->label('Overtime')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('gross_salary')
                    ->label('Gross Salary')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('working_days')
                    ->numeric()
                    ->default(0),

                TextInput::make('present_days')
                    ->numeric()
                    ->default(0),

                TextInput::make('absent_days')
                    ->numeric()
                    ->default(0),

                TextInput::make('no_pay_days')
                    ->label('No-Pay Days')
                    ->numeric()
                    ->default(0),

                TextInput::make('epf_employee')
                    ->label('EPF - Employee')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('epf_employer')
                    ->label('EPF - Employer')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('etf_employer')
                    ->label('ETF - Employer')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('deduction')
                    ->label('Other Deduction')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('total_deductions')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                TextInput::make('net_salary')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0),

                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'processed' => 'Processed',
                        'paid' => 'Paid',
                    ])
                    ->required()
                    ->default('draft'),

                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('period')
                    ->label('Period')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('gross_salary')
                    ->label('Gross Salary')
                    ->money('LKR')
                    ->sortable(),

                TextColumn::make('total_deductions')
                    ->label('Deductions')
                    ->money('LKR')
                    ->sortable(),

                TextColumn::make('net_salary')
                    ->label('Net Salary')
                    ->money('LKR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                Action::make('payslip')
                    ->label('Payslip')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Payroll $record): string => route('payroll.payslip', $record))
                    ->openUrlInNewTab(),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ])
            ->defaultSort('period', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}



