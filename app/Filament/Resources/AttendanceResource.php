<?php

namespace App\Filament\Resources;
use Filament\Support\Icons\Heroicon;

use BackedEnum;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AttendanceResource extends Resource
{

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $model = Attendance::class;

    protected static ?string $navigationLabel = 'Attendance';

    protected static ?string $modelLabel = 'Attendance';

    protected static ?string $pluralModelLabel = 'Attendance';

    protected static UnitEnum|string|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Employee')
                ->relationship('employee', 'name')
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('attendance_date')
                ->label('Date')
                ->default(now())
                ->required(),

            TimePicker::make('check_in')
                ->label('Check In'),

            TimePicker::make('check_out')
                ->label('Check Out'),

            Select::make('status')
                ->options([
                    'present' => 'Present',
                    'absent' => 'Absent',
                    'late' => 'Late',
                    'half_day' => 'Half Day',
                    'leave' => 'Leave',
                    'holiday' => 'Holiday',
                ])
                ->default('present')
                ->required(),

            TextInput::make('late_minutes')
                ->label('Late Minutes')
                ->numeric()
                ->minValue(0)
                ->default(0),

            TextInput::make('working_hours')
                ->label('Working Hours')
                ->numeric()
                ->step(0.01)
                ->minValue(0),

            Textarea::make('notes')
                ->label('Notes')
                ->columnSpanFull(),
        ])->columns([
            'default' => 1,
            'md' => 2,
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attendance_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('check_in')
                    ->label('Check In'),

                TextColumn::make('check_out')
                    ->label('Check Out'),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('late_minutes')
                    ->label('Late')
                    ->suffix(' min'),

                TextColumn::make('working_hours')
                    ->label('Hours')
                    ->suffix(' hrs'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('attendance_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
