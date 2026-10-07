<?php

namespace App\Filament\Resources;
use Filament\Support\Icons\Heroicon;

use BackedEnum;

use UnitEnum;

use App\Filament\Resources\SmsPackageResource\Pages;
use App\Models\SmsPackage;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SmsPackageResource extends Resource
{

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;


    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $model = SmsPackage::class;

    protected static ?string $navigationLabel = 'SMS Packages';

    protected static ?string $pluralModelLabel = 'SMS Packages';

    protected static ?string $modelLabel = 'SMS Package';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->helperText('Example: 1000 SMS'),

            TextInput::make('sms_count')
                ->label('SMS Count')
                ->numeric()
                ->minValue(1)
                ->required(),

            TextInput::make('price')
                ->label('Price (LKR)')
                ->numeric()
                ->minValue(0)
                ->required()
                ->prefix('LKR'),

            TextInput::make('validity_days')
                ->label('Validity (Days)')
                ->numeric()
                ->minValue(1)
                ->nullable()
                ->helperText('Leave empty if the package does not expire.'),

            Toggle::make('enabled')
                ->label('Available for customers')
                ->default(true),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->required(),

            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns([
            'default' => 1,
            'xl' => 2,
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sms_count')
                    ->label('SMS')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(
                        fn ($state) => 'LKR ' . number_format((float) $state, 2)
                    )
                    ->sortable(),

                TextColumn::make('validity_days')
                    ->label('Validity')
                    ->formatStateUsing(
                        fn ($state) => $state ? $state . ' days' : 'No expiry'
                    ),

                IconColumn::make('enabled')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->sortable(),

                TextColumn::make('updated_at')
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
            ->defaultSort('sort_order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsPackages::route('/'),
            'create' => Pages\CreateSmsPackage::route('/create'),
            'edit' => Pages\EditSmsPackage::route('/{record}/edit'),
        ];
    }
}



