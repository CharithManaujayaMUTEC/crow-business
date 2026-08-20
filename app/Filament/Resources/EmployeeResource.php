<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationLabel = 'Employees';
    protected static ?string $pluralModelLabel = 'Employees';
    protected static ?string $modelLabel = 'Employee';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('employee_no')->required(),
TextInput::make('name')->required(),
TextInput::make('position'),
TextInput::make('phone'),
TextInput::make('email')->email(),
DatePicker::make('join_date'),
TextInput::make('basic_salary')->numeric()->prefix('LKR')->required(),
TextInput::make('allowance')->numeric()->default(0),
TextInput::make('deduction')->numeric()->default(0),
Select::make('status')->options(['active'=>'Active','inactive'=>'Inactive'])->default('active')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
TextColumn::make('employee_no')->searchable(),
TextColumn::make('name')->searchable(),
TextColumn::make('position')->searchable(),
TextColumn::make('basic_salary')->money('LKR'),
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
            'index' => Pages\ListEmployee::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
