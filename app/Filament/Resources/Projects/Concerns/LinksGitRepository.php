<?php

namespace App\Filament\Resources\Projects\Concerns;

use App\Services\GitHubService;
use Filament\Notifications\Notification;

trait LinksGitRepository
{
    /** Resolve the project's repository URL and pull its commit history. */
    protected function linkGitRepository(): void
    {
        set_time_limit(300);

        try {
            [$stored, $errors] = (new GitHubService())->linkProject($this->record);

            if ($errors) {
                throw new \RuntimeException(implode(' | ', $errors));
            }

            if (filled($this->record->git_repository_url)) {
                Notification::make()->title("Repository linked - {$stored} commits synced")->success()->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Could not sync the Git repository')
                ->body(GitHubService::describeError($e) . ' (check Settings > GitHub Settings)')
                ->warning()
                ->send();
        }
    }
}
