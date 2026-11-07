<?php

namespace App\Filament\Pages;

use App\Models\GithubSetting;
use App\Services\GitHubService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class GithubSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracketSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'GitHub Settings';

    protected static ?string $title = 'GitHub Settings';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament.pages.github-settings';

    public ?array $data = [];

    /** The token holds org-wide read access, so only super admins may manage it. */
    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('super_admin');
    }

    public function mount(): void
    {
        $settings = GithubSetting::current();

        $this->form->fill([
            'enabled' => $settings->enabled,
            'organization' => $settings->organization,
            'api_url' => $settings->api_url,
            'auto_sync' => $settings->auto_sync,
            'initial_days_back' => $settings->initial_days_back,
            'token' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $hasToken = filled(GithubSetting::current()->token);

        return $schema
            ->components([
                Section::make('Connection')
                    ->description('Connect your GitHub organization to import repositories and commit history.')
                    ->schema([
                        Toggle::make('enabled')->label('Enable GitHub integration'),

                        TextInput::make('organization')
                            ->label('Organization')
                            ->placeholder('my-org')
                            ->required(),

                        TextInput::make('token')
                            ->label('Personal access token')
                            ->password()
                            ->revealable()
                            ->required(! $hasToken)
                            ->placeholder($hasToken ? 'Saved - leave blank to keep the current token' : 'ghp_... or github_pat_...')
                            ->helperText('Fine-grained token with read-only "Contents" and "Metadata" access to the organization repositories. Stored encrypted.'),

                        TextInput::make('api_url')
                            ->label('API URL')
                            ->url()
                            ->default('https://api.github.com')
                            ->helperText('Change only for GitHub Enterprise Server.')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Sync')
                    ->schema([
                        Toggle::make('auto_sync')
                            ->label('Sync automatically every hour')
                            ->helperText('Requires the Laravel scheduler (php artisan schedule:run) to be running.'),

                        TextInput::make('initial_days_back')
                            ->label('Initial history (days)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(3650)
                            ->required()
                            ->helperText('How far back to import commits the first time a repository is synced.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (blank($data['token'] ?? null)) {
            unset($data['token']); // keep the stored token
        }

        GithubSetting::current()->update($data);

        $this->form->fill(array_merge($data, ['token' => null]));

        Notification::make()->title('GitHub settings saved')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->label('Test connection')
                ->icon(Heroicon::OutlinedSignal)
                ->color('gray')
                ->action(fn () => $this->run(fn (GitHubService $s) => $s->testConnection())),

            Action::make('syncRepos')
                ->label('Sync repositories')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(fn () => $this->run(
                    fn (GitHubService $s) => $s->syncRepositories() . ' repositories synced.'
                )),

            Action::make('syncCommits')
                ->label('Sync commits now')
                ->icon(Heroicon::OutlinedCloudArrowDown)
                ->action(fn () => $this->run(function (GitHubService $s) {
                    [$repos, $commits, $errors] = $s->syncCommits();

                    if ($errors) {
                        throw new \RuntimeException(
                            "{$commits} commits from {$repos} repos; errors: " . implode(' | ', $errors)
                        );
                    }

                    return "{$commits} commits processed across {$repos} repositories.";
                })),
        ];
    }

    private function run(callable $callback): void
    {
        set_time_limit(300);

        try {
            $message = $callback(new GitHubService(GithubSetting::current()->fresh()));
            Notification::make()->title($message)->success()->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('GitHub request failed')
                ->body(GitHubService::describeError($e))
                ->danger()
                ->send();
        }
    }
}
