<?php

namespace App\Filament\Resources;

use UnitEnum;

use App\Filament\Resources\SmsNotificationTemplateResource\Pages;
use App\Models\SmsNotificationTemplate;
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

class SmsNotificationTemplateResource extends Resource
{

    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $model = SmsNotificationTemplate::class;

    protected static ?string $navigationLabel = 'SMS Templates';

    protected static ?string $pluralModelLabel = 'SMS Templates';

    protected static ?string $modelLabel = 'SMS Template';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText('Unique internal key. Example: payment_reminder_3_days.'),

            TextInput::make('name')
                ->required(),

            Toggle::make('enabled')
                ->label('Enable this notification')
                ->default(true),

            TextInput::make('trigger_days')
                ->label('Days relative to invoice due date')
                ->numeric()
                ->nullable()
                ->helperText(
                    'Payment reminders only: 3 = 3 days before, 0 = due today, -3 = 3 days overdue.'
                ),

            TextInput::make('cooldown_minutes')
                ->label('Minimum interval (minutes)')
                ->numeric()
                ->minValue(0)
                ->default(1440)
                ->required()
                ->helperText(
                    'Prevents the same notification from being sent again during this interval.'
                ),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->required(),

            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),

            Textarea::make('message')
                ->required()
                ->rows(5)
                ->columnSpanFull()
                ->helperText(
                    'Variables: {customer_name}, {company_name}, {invoice_number}, {balance}, {balance_formatted}, {due_date}, {days_until_due}, {app_name}.'
                ),
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

                TextColumn::make('key')
                    ->searchable(),

                IconColumn::make('enabled')
                    ->boolean(),

                TextColumn::make('trigger_days')
                    ->label('Due Offset')
                    ->sortable(),

                TextColumn::make('cooldown_minutes')
                    ->label('Cooldown')
                    ->formatStateUsing(
                        fn ($state) => ((int) $state) . ' min'
                    )
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
            'index' => Pages\ListSmsNotificationTemplates::route('/'),
            'create' => Pages\CreateSmsNotificationTemplate::route('/create'),
            'edit' => Pages\EditSmsNotificationTemplate::route('/{record}/edit'),
        ];
    }
}



