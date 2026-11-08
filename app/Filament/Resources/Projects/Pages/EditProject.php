<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\Concerns\LinksGitRepository;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProject extends EditRecord
{
    use LinksGitRepository;

    protected static string $resource = ProjectResource::class;

    protected function afterSave(): void
    {
        if ($this->record->wasChanged('git_repository_url')) {
            $this->linkGitRepository();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncCommits')
                ->label('Sync commits')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->visible(fn () => filled($this->record->git_repository_url))
                ->action(function () {
                    $this->linkGitRepository();
                    $this->dispatch('$refresh');
                }),

            DeleteAction::make(),
        ];
    }
}
