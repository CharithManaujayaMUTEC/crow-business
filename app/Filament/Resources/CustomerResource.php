<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationLabel = 'Customers';
    protected static ?string $pluralModelLabel = 'Customers';
    protected static ?string $modelLabel = 'Customer';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
TextInput::make('company_name'),
TextInput::make('email')->email(),
TextInput::make('phone')->required(),
TextInput::make('whatsapp'),
Textarea::make('address')->columnSpanFull(),
Textarea::make('notes')->columnSpanFull(),
Select::make('status')->options(['active'=>'Active','inactive'=>'Inactive'])->default('active')
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
TextColumn::make('name')->searchable()->sortable(),
TextColumn::make('company_name')->searchable(),
TextColumn::make('phone')->searchable(),
TextColumn::make('email')->searchable(),
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
            'index' => Pages\ListCustomer::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
