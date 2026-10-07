<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveTypeResource\Pages;
use App\Models\LeaveType;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use UnitEnum;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Leave Types';

    protected static ?string $modelLabel = 'Leave Type';

    protected static ?string $pluralModelLabel = 'Leave Types';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                TextInput::make('allocation')
                    ->label('Allocation')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->minValue(0),

                Select::make('allocation_period')
                    ->label('Allocation Period')
                    ->options([
                        'yearly' => 'Yearly',
                        'monthly' => 'Monthly',
                    ])
                    ->required()
                    ->default('yearly'),

                Toggle::make('is_paid')
                    ->label('Paid Leave')
                    ->default(true),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('allocation')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('allocation_period')
                    ->label('Period')
                    ->badge(),

                IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'edit' => Pages\EditLeaveType::route('/{record}/edit'),
        ];
    }
}

