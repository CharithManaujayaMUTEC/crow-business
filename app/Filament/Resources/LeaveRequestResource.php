<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationLabel = 'Leave Requests';

    protected static ?string $modelLabel = 'Leave Request';

    protected static ?string $pluralModelLabel = 'Leave Requests';

    protected static UnitEnum|string|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 31;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Employee')
                ->relationship('employee', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('leave_type_id')
                ->label('Leave Type')
                ->relationship('leaveType', 'name')
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('from_date')
                ->label('From')
                ->required(),

            DatePicker::make('to_date')
                ->label('To')
                ->required()
                ->afterOrEqual('from_date'),

            TextInput::make('days')
                ->numeric()
                ->minValue(0.5)
                ->step(0.5)
                ->required(),

            Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'cancelled' => 'Cancelled',
                ])
                ->default('pending')
                ->required(),

            Select::make('approved_by')
                ->label('Approved By')
                ->relationship('approver', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            Textarea::make('reason')
                ->label('Reason')
                ->columnSpanFull(),

            Textarea::make('approval_notes')
                ->label('Approval Notes')
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
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->sortable(),

                TextColumn::make('from_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('to_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('days')
                    ->label('Days'),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
