<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecurringServiceResource\Pages;
use App\Models\RecurringService;
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
use Filament\Forms\Components\Toggle;

class RecurringServiceResource extends Resource
{
    protected static ?string $model = RecurringService::class;
    protected static ?string $navigationLabel = 'Recurring Services';
    protected static ?string $pluralModelLabel = 'Recurring Services';
    protected static ?string $modelLabel = 'RecurringService';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')->relationship('customer','name')->searchable()->preload()->required(),
Select::make('service_id')->relationship('service','name')->searchable()->preload()->required(),
TextInput::make('amount')->numeric()->prefix('LKR')->required(),
Select::make('frequency')->options(['monthly'=>'Monthly','yearly'=>'Yearly'])->default('monthly'),
TextInput::make('billing_day')->numeric()->minValue(1)->maxValue(28)->default(1),
DatePicker::make('start_date')->required(),
DatePicker::make('next_billing_date')->required(),
Select::make('status')->options(['active'=>'Active','paused'=>'Paused','cancelled'=>'Cancelled'])->default('active'),
Toggle::make('auto_invoice')->default(true)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
TextColumn::make('customer.name')->searchable(),
TextColumn::make('service.name')->searchable(),
TextColumn::make('amount')->money('LKR'),
TextColumn::make('next_billing_date')->date(),
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
            'index' => Pages\ListRecurringService::route('/'),
            'create' => Pages\CreateRecurringService::route('/create'),
            'edit' => Pages\EditRecurringService::route('/{record}/edit'),
        ];
    }
}
