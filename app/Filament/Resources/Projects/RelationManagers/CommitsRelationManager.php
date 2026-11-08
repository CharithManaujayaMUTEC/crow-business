<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Models\CompanySetting;
use App\Models\GithubCommit;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommitsRelationManager extends RelationManager
{
    protected static string $relationship = 'commits';

    protected static ?string $title = 'Commits & Development Hours';

    public function table(Table $table): Table
    {
        $rate = (float) (CompanySetting::query()->first()?->development_hourly_rate ?? 0);

        return $table
            ->description(
                $rate > 0
                    ? 'Hourly rate: LKR ' . number_format($rate, 2) . ' (change in Company Settings).'
                    : 'No hourly rate set yet - add one in Company Settings to calculate costs.'
            )
            ->columns([
                TextColumn::make('author_name')
                    ->label('Author')
                    ->description(fn (GithubCommit $r) => $r->author_login ? '@' . $r->author_login : $r->author_email)
                    ->searchable(['author_name', 'author_login', 'author_email']),

                TextColumn::make('message')
                    ->label('Commit message')
                    ->formatStateUsing(fn (string $state) => strtok($state, "\n"))
                    ->limit(80)
                    ->tooltip(fn (GithubCommit $r) => $r->message)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('sha')
                    ->label('SHA')
                    ->formatStateUsing(fn (string $state) => substr($state, 0, 7))
                    ->url(fn (GithubCommit $r) => $r->url, shouldOpenInNewTab: true)
                    ->fontFamily('mono'),

                TextColumn::make('committed_at')->label('Committed')->dateTime()->sortable(),

                TextInputColumn::make('hours')
                    ->label('Hours')
                    ->type('number')
                    ->step(0.25)
                    ->rules(['nullable', 'numeric', 'min:0', 'max:999'])
                    ->summarize(Sum::make()->label('Total hours')),

                TextColumn::make('cost')
                    ->label('Cost')
                    ->state(fn (GithubCommit $r) => (float) $r->hours * $rate)
                    ->money('LKR'),
            ])
            ->filters([
                Filter::make('committed_between')
                    ->schema([DatePicker::make('from'), DatePicker::make('until')])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('committed_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('committed_at', '<=', $d))),

                Filter::make('no_hours')
                    ->label('Hours not set')
                    ->query(fn (Builder $query) => $query->whereNull('hours')),
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sync commits')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->action(function () {
                        set_time_limit(300);

                        try {
                            [$stored, $errors] = (new \App\Services\GitHubService())->linkProject($this->getOwnerRecord());
                        } catch (\Throwable $e) {
                            [$stored, $errors] = [0, [\App\Services\GitHubService::describeError($e)]];
                        }

                        \Filament\Notifications\Notification::make()
                            ->title($errors ? 'Sync failed' : "{$stored} commits synced")
                            ->body($errors ? implode(' | ', $errors) : null)
                            ->color($errors ? 'danger' : 'success')
                            ->send();
                    })
                    ->visible(fn () => filled($this->getOwnerRecord()->git_repository_url)),
            ])
            ->emptyStateHeading('No commits yet')
            ->emptyStateDescription('Set the Git Repository URL on this project and save to import its commit history.')
            ->defaultSort('committed_at', 'desc');
    }
}
