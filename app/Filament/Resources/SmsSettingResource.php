<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsSettingResource\Pages;
use App\Models\SmsSetting;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;

class SmsSettingResource extends Resource
{
    protected static ?string $model = SmsSetting::class;
    protected static ?string $navigationLabel = 'SMS Settings';
    protected static ?string $pluralModelLabel = 'SMS Settings';
    protected static ?string $modelLabel = 'SmsSetting';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('enabled')->label('Enable SMS sending'),
TextInput::make('api_url'),
TextInput::make('api_user_id'),
TextInput::make('api_key')->password()->revealable(),
TextInput::make('sender_id')->default('Crow.lk'),
TextInput::make('country_code')->default('94')
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
IconColumn::make('enabled')->boolean(),
TextColumn::make('sender_id'),
TextColumn::make('country_code')
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
            'index' => Pages\ListSmsSetting::route('/'),
            'create' => Pages\CreateSmsSetting::route('/create'),
            'edit' => Pages\EditSmsSetting::route('/{record}/edit'),
        ];
    }
}
