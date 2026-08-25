<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsLogResource\Pages;
use App\Models\SmsLog;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;

class SmsLogResource extends Resource
{
    protected static ?string $model = SmsLog::class;
    protected static ?string $navigationLabel = 'SMS Logs';
    protected static ?string $pluralModelLabel = 'SMS Logs';
    protected static ?string $modelLabel = 'SmsLog';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')->relationship('customer','name')->searchable()->preload(),
TextInput::make('phone')->required(),
TextInput::make('type')->required(),
TextInput::make('reference_type'),
TextInput::make('reference_id')->numeric(),
Textarea::make('message')->required()->columnSpanFull(),
Select::make('status')->options(['sent'=>'Sent','failed'=>'Failed','disabled'=>'Disabled'])->required(),
Textarea::make('provider_response')->columnSpanFull(),
DateTimePicker::make('sent_at')
])
->columns([
    'default' => 1,
    'xl' => 2,
]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
TextColumn::make('customer.name')->searchable(),
TextColumn::make('phone'),
TextColumn::make('type')->badge(),
TextColumn::make('status')->badge(),
TextColumn::make('sent_at')->dateTime()
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
            'index' => Pages\ListSmsLog::route('/'),
            'create' => Pages\CreateSmsLog::route('/create'),
            'edit' => Pages\EditSmsLog::route('/{record}/edit'),
        ];
    }
}
