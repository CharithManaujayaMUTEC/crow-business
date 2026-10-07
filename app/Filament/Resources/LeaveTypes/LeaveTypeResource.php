<?php

namespace App\Filament\Resources\LeaveTypes;

use App\Filament\Resources\LeaveTypes\Pages\CreateLeaveType;
use App\Filament\Resources\LeaveTypes\Pages\EditLeaveType;
use App\Filament\Resources\LeaveTypes\Pages\ListLeaveTypes;
use App\Filament\Resources\LeaveTypes\Schemas\LeaveTypeForm;
use App\Filament\Resources\LeaveTypes\Tables\LeaveTypesTable;
use App\Models\LeaveType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LeaveTypeResource extends Resource
{
    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 40;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $model = LeaveType::class;

    protected static ?string $navigationLabel = 'Leave Types';

    protected static ?string $modelLabel = 'Leave Type';

    protected static ?string $pluralModelLabel = 'Leave Types';

    public static function form(Schema $schema): Schema
    {
        return LeaveTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaveTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeaveTypes::route('/'),
            'create' => CreateLeaveType::route('/create'),
            'edit' => EditLeaveType::route('/{record}/edit'),
        ];
    }
}