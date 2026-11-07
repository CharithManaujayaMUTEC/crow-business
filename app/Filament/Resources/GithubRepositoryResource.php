<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GithubRepositoryResource\Pages;
use App\Models\GithubRepository;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class GithubRepositoryResource extends Resource
{
    protected static ?string $model = GithubRepository::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|UnitEnum|null $navigationGroup = 'Development';

    protected static ?string $navigationLabel = 'Repositories';

    protected static ?string $modelLabel = 'Repository';

    protected static ?string $pluralModelLabel = 'Repositories';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Repository')
                    ->url(fn (GithubRepository $r) => $r->html_url, shouldOpenInNewTab: true)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('default_branch')->label('Branch'),
                IconColumn::make('is_private')->label('Private')->boolean(),
                IconColumn::make('is_archived')->label('Archived')->boolean(),
                ToggleColumn::make('is_tracked')->label('Track commits'),
                TextColumn::make('commits_count')->counts('commits')->label('Commits')->sortable(),
                TextColumn::make('last_synced_at')->label('Last synced')->dateTime()->sortable(),
                TextColumn::make('last_sync_error')->label('Last error')->color('danger')->limit(40)
                    ->tooltip(fn (GithubRepository $r) => $r->last_sync_error),
            ])
            ->defaultSort('full_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGithubRepositories::route('/'),
        ];
    }
}
