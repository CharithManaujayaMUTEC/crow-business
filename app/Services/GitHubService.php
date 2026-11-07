<?php

namespace App\Services;

use App\Models\GithubCommit;
use App\Models\GithubRepository;
use App\Models\GithubSetting;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubService
{
    private const PER_PAGE = 100;
    private const MAX_PAGES = 50;

    private GithubSetting $settings;

    public function __construct(?GithubSetting $settings = null)
    {
        $this->settings = $settings ?? GithubSetting::current();
    }

    private function client(): PendingRequest
    {
        if (blank($this->settings->token) || blank($this->settings->organization)) {
            throw new RuntimeException('GitHub organization and token must be configured first.');
        }

        return Http::withToken($this->settings->token)
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->baseUrl(rtrim($this->settings->api_url ?: 'https://api.github.com', '/'))
            ->timeout(30);
    }

    /** Verify token + organization; returns a human readable summary. */
    public function testConnection(): string
    {
        $org = $this->client()->get("/orgs/{$this->settings->organization}")->throw()->json();

        return sprintf(
            'Connected to "%s" (%s public repos).',
            $org['login'] ?? $this->settings->organization,
            $org['public_repos'] ?? '?'
        );
    }

    /** Import/refresh the organization's repositories. Returns the number processed. */
    public function syncRepositories(): int
    {
        $count = 0;

        for ($page = 1; $page <= self::MAX_PAGES; $page++) {
            $repos = $this->client()
                ->get("/orgs/{$this->settings->organization}/repos", [
                    'type' => 'all', 'per_page' => self::PER_PAGE, 'page' => $page,
                ])
                ->throw()->json();

            foreach ($repos as $repo) {
                $model = GithubRepository::firstOrNew(['github_id' => $repo['id']]);
                $model->fill([
                    'name' => $repo['name'],
                    'full_name' => $repo['full_name'],
                    'default_branch' => $repo['default_branch'] ?? null,
                    'html_url' => $repo['html_url'] ?? null,
                    'description' => $repo['description'] ?? null,
                    'is_private' => (bool) ($repo['private'] ?? false),
                    'is_archived' => (bool) ($repo['archived'] ?? false),
                ]);
                $model->save(); // new repos keep the default is_tracked = true
                $count++;
            }

            if (count($repos) < self::PER_PAGE) {
                break;
            }
        }

        return $count;
    }

    /**
     * Sync default-branch commits for one repo, or all tracked repos.
     * Returns [repos synced, commits stored, errors[]].
     */
    public function syncCommits(?GithubRepository $only = null): array
    {
        $repos = $only
            ? collect([$only])
            : GithubRepository::where('is_tracked', true)->where('is_archived', false)->get();

        $stored = 0;
        $errors = [];

        foreach ($repos as $repo) {
            try {
                $stored += $this->syncRepoCommits($repo);
                $repo->update(['last_synced_at' => now(), 'last_sync_error' => null]);
            } catch (\Throwable $e) {
                $message = self::describeError($e);
                $repo->update(['last_sync_error' => $message]);
                $errors[] = "{$repo->full_name}: {$message}";
            }
        }

        $this->settings->update(['last_synced_at' => now()]);

        return [$repos->count(), $stored, $errors];
    }

    private function syncRepoCommits(GithubRepository $repo): int
    {
        $since = $repo->last_synced_at
            ? $repo->last_synced_at->copy()->subDay()
            : now()->subDays(max(1, (int) $this->settings->initial_days_back));

        $stored = 0;

        for ($page = 1; $page <= self::MAX_PAGES; $page++) {
            $response = $this->client()->get("/repos/{$repo->full_name}/commits", array_filter([
                'sha' => $repo->default_branch,
                'since' => $since->toIso8601String(),
                'per_page' => self::PER_PAGE,
                'page' => $page,
            ]));

            if ($response->status() === 409) { // empty repository
                return $stored;
            }

            $commits = $response->throw()->json();

            $rows = collect($commits)->map(fn (array $c) => [
                'github_repository_id' => $repo->id,
                'sha' => $c['sha'],
                'message' => $c['commit']['message'] ?? '',
                'author_name' => $c['commit']['author']['name'] ?? null,
                'author_email' => $c['commit']['author']['email'] ?? null,
                'author_login' => $c['author']['login'] ?? null,
                'author_avatar' => $c['author']['avatar_url'] ?? null,
                'committer_name' => $c['commit']['committer']['name'] ?? null,
                'committer_login' => $c['committer']['login'] ?? null,
                'url' => $c['html_url'] ?? null,
                'committed_at' => isset($c['commit']['author']['date'])
                    ? Carbon::parse($c['commit']['author']['date'])->utc()->toDateTimeString()
                    : null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            if ($rows) {
                GithubCommit::upsert(
                    $rows,
                    ['github_repository_id', 'sha'],
                    ['message', 'author_name', 'author_email', 'author_login', 'author_avatar',
                     'committer_name', 'committer_login', 'url', 'committed_at', 'updated_at']
                );
                $stored += count($rows);
            }

            if (count($commits) < self::PER_PAGE) {
                break;
            }
        }

        return $stored;
    }

    public static function describeError(\Throwable $e): string
    {
        if ($e instanceof RequestException) {
            return sprintf('HTTP %s - %s', $e->response->status(), $e->response->json('message') ?? 'request failed');
        }

        return $e->getMessage();
    }
}
