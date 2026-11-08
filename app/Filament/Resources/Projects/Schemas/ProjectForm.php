<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('name')
                    ->label('Project Name')
                    ->required()
                    ->maxLength(255),

                Select::make('status')
                    ->options([
                        'planned' => 'Planned',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('planned')
                    ->required(),

                TextInput::make('project_value')
                    ->label('Project Value')
                    ->numeric()
                    ->prefix('LKR')
                    ->default(0)
                    ->required(),

                TextInput::make('progress')
                    ->label('Progress')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->default(0)
                    ->required(),

                TextInput::make('git_repository_url')
                    ->label('Git Repository URL')
                    ->placeholder('https://github.com/your-org/repository')
                    ->helperText('Must be a repository in the organization configured under GitHub Settings. Commits are imported when you save; set hours per commit in the Commits tab below.')
                    ->rules([
                        fn () => function (string $attribute, $value, \Closure $fail) {
                            if (filled($value) && ! \App\Services\GitHubService::parseRepo($value)) {
                                $fail('Enter a GitHub repository URL like https://github.com/org/repo.');
                            }
                        },
                    ])
                    ->maxLength(255)
                    ->columnSpanFull(),

                DatePicker::make('start_date')
                    ->label('Start Date'),

                DatePicker::make('expected_end_date')
                    ->label('Expected End Date'),

                DatePicker::make('completed_date')
                    ->label('Completed Date'),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}