<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanySettingResource\Pages;
use App\Models\CompanySetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanySettingResource extends Resource
{
    protected static ?string $model = CompanySetting::class;

    protected static ?string $navigationLabel = 'Company Settings';

    protected static ?string $modelLabel = 'Company Setting';

    protected static ?string $pluralModelLabel = 'Company Settings';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Company Details')
                ->schema([
                    TextInput::make('company_name')
                        ->required(),

                    FileUpload::make('letterhead_path')
                        ->label('Letterhead')
                        ->image()
                        ->disk('public')
                        ->directory('images')
                        ->imageEditor()
                        ->helperText('Used as the full-page background for quotations, invoices and payment receipts.'),
                ])
                ->columns(2),

            Section::make('Bank Details')
                ->schema([
                    TextInput::make('bank_account_name')
                        ->label('Account Name')
                        ->required(),

                    TextInput::make('bank_name')
                        ->label('Bank')
                        ->required(),

                    TextInput::make('bank_branch')
                        ->label('Branch')
                        ->required(),

                    TextInput::make('bank_account_number')
                        ->label('Account Number')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable(),

                TextColumn::make('bank_name')
                    ->label('Bank'),

                TextColumn::make('bank_branch')
                    ->label('Branch'),

                TextColumn::make('bank_account_number')
                    ->label('Account Number'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanySettings::route('/'),
            'edit' => Pages\EditCompanySetting::route('/{record}/edit'),
        ];
    }
}