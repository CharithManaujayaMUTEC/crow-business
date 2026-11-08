<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\Concerns\LinksGitRepository;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    use LinksGitRepository;

    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        if (filled($this->record->git_repository_url)) {
            $this->linkGitRepository();
        }
    }
}
