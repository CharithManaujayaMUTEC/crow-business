<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeaveRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('leave_type_id')
                    ->label('Leave Type')
                    ->relationship(
                        'leaveType',
                        'name',
                        modifyQueryUsing: fn ($query) => $query->where('is_active', true)
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('from_date')
                    ->label('From Date')
                    ->required()
                    ->live(),

                DatePicker::make('to_date')
                    ->label('To Date')
                    ->required()
                    ->live(),

                TextInput::make('days')
                    ->label('Leave Days')
                    ->numeric()
                    ->minValue(0.5)
                    ->step(0.5)
                    ->required()
                    ->helperText('Enter 0.5 for a half-day.'),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),

                Textarea::make('reason')
                    ->label('Reason')
                    ->rows(4)
                    ->columnSpanFull(),

                Textarea::make('approval_notes')
                    ->label('Approval Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}