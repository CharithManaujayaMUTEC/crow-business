<?php

namespace App\Filament\Resources\LeaveTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeaveTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Leave Type Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                TextInput::make('allocation')
                    ->label('Allocation')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.5)
                    ->required()
                    ->default(0),

                Select::make('allocation_period')
                    ->label('Allocation Period')
                    ->options([
                        'yearly' => 'Yearly',
                        'monthly' => 'Monthly',
                    ])
                    ->default('yearly')
                    ->required(),

                Toggle::make('is_paid')
                    ->label('Paid Leave')
                    ->default(true),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}