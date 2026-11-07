<?php

namespace App\Console\Commands;

use App\Models\GithubSetting;
use App\Services\GitHubService;
use Illuminate\Console\Command;

class SyncGithub extends Command
{
    protected $signature = 'crow:github-sync {--scheduled : Respect the auto-sync setting}';
    protected $description = 'Sync GitHub organization repositories and commits';

    public function handle(): int
    {
        $settings = GithubSetting::current();

        if (! $settings->enabled || ($this->option('scheduled') && ! $settings->auto_sync)) {
            $this->info('GitHub sync is disabled.');
            return self::SUCCESS;
        }

        $service = new GitHubService($settings);

        try {
            $this->info('Repositories synced: ' . $service->syncRepositories());
            [$repos, $commits, $errors] = $service->syncCommits();
        } catch (\Throwable $e) {
            $this->error(GitHubService::describeError($e));
            return self::FAILURE;
        }

        $this->info("Commits processed: {$commits} across {$repos} repositories.");
        foreach ($errors as $error) {
            $this->warn($error);
        }

        return $errors ? self::FAILURE : self::SUCCESS;
    }
}
