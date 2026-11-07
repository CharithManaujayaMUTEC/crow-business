<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GithubCommitResource\Pages;
use App\Models\GithubCommit;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GithubCommitResource extends Resource
{
    protected static ?string $model = GithubCommit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracket;

    protected static string|UnitEnum|null $navigationGroup = 'Development';

    protected static ?string $navigationLabel = 'Commits';

    protected static ?string $modelLabel = 'Commit';

    protected static ?string $pluralModelLabel = 'Commits';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('repository'))
            ->columns([
                ImageColumn::make('author_avatar')->label('')->circular()->size(32),

                TextColumn::make('author_name')
                    ->label('Author')
                    ->description(fn (GithubCommit $r) => $r->author_login ? '@' . $r->author_login : $r->author_email)
                    ->searchable(['author_name', 'author_login', 'author_email']),

                TextColumn::make('repository.name')->label('Repository')->badge()->sortable(),

                TextColumn::make('message')
                    ->label('Commit message')
                    ->formatStateUsing(fn (string $state) => strtok($state, "\n"))
                    ->limit(90)
                    ->tooltip(fn (GithubCommit $r) => $r->message)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('sha')
                    ->label('SHA')
                    ->formatStateUsing(fn (string $state) => substr($state, 0, 7))
                    ->copyable()
                    ->copyableState(fn (GithubCommit $r) => $r->sha)
                    ->fontFamily('mono'),

                TextColumn::make('committed_at')->label('Committed')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('github_repository_id')
                    ->label('Repository')
                    ->relationship('repository', 'name'),

                SelectFilter::make('author_login')
                    ->label('Author')
                    ->options(fn () => GithubCommit::query()
                        ->whereNotNull('author_login')
                        ->distinct()
                        ->orderBy('author_login')
                        ->pluck('author_login', 'author_login')
                        ->all())
                    ->searchable(),

                Filter::make('committed_between')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('committed_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('committed_at', '<=', $d))),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('View on GitHub')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (GithubCommit $r) => $r->url, shouldOpenInNewTab: true)
                    ->visible(fn (GithubCommit $r) => filled($r->url)),
            ])
            ->defaultSort('committed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGithubCommits::route('/'),
        ];
    }
}
