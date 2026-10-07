<?php

namespace App\Filament\Resources;
use Filament\Support\Icons\Heroicon;

use BackedEnum;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollCalculationService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PayrollResource extends Resource
{
    protected static string|UnitEnum|null $navigationGroup = 'HR';
    protected static ?int $navigationSort = 50;



    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $model = Payroll::class;



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
                    ->live()
                    ->required(),

                TextInput::make('period')
                    ->label('Payroll Period')
                    ->placeholder('2026-10')
                    ->default(now()->format('Y-m'))
                    ->live()
                    ->required()
                    ->maxLength(7)
                    ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                    ->helperText('Use YYYY-MM format.'),

                DatePicker::make('payment_date')
                    ->label('Payment Date'),

                Placeholder::make('calculation_info')
                    ->label('Calculation')
                    ->content(function ($get): string {
                        $employeeId = $get('employee_id');
                        $period = $get('period');

                        if (!$employeeId || !$period) {
                            return 'Select an employee and payroll period.';
                        }

                        try {
                            $employee = Employee::find($employeeId);

                            if (!$employee) {
                                return 'Employee not found.';
                            }

                            $calculation = app(PayrollCalculationService::class)->calculate(
                                $employee,
                                $period,
                                (float) ($get('overtime_amount') ?? 0),
                                (float) ($get('deduction') ?? 0),
                            );

                            return sprintf(
                                'Working Days: %d | Present: %d | Absent: %d | No-Pay: %.2f | Gross: LKR %s | Net: LKR %s',
                                $calculation['working_days'],
                                $calculation['present_days'],
                                $calculation['absent_days'],
                                $calculation['no_pay_days'],
                                number_format($calculation['gross_salary'], 2),
                                number_format($calculation['net_salary'], 2),
                            );
                        } catch (\Throwable $e) {
                            return 'Calculation unavailable until the payroll period is valid.';
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('basic_salary')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('allowance')
                    ->label('Allowance')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('fixed_allowance')
                    ->label('Fixed Allowance')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('other_allowances')
                    ->label('Other Allowances')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('overtime_amount')
                    ->label('Overtime')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0)
                    ->live(),

                TextInput::make('gross_salary')
                    ->label('Gross Salary')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('working_days')
                    ->numeric()
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('present_days')
                    ->numeric()
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('absent_days')
                    ->numeric()
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('no_pay_days')
                    ->label('No-Pay Days')
                    ->numeric()
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('epf_employee')
                    ->label('EPF - Employee')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('epf_employer')
                    ->label('EPF - Employer')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('etf_employer')
                    ->label('ETF - Employer')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('deduction')
                    ->label('Other Deduction')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0)
                    ->live(),

                TextInput::make('total_deductions')
                    ->label('Total Deductions')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

                TextInput::make('net_salary')
                    ->label('Net Salary')
                    ->numeric()
                    ->prefix('LKR')
                    ->readOnly()
                    ->dehydrated(),

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
