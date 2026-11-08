<?php

namespace App\Filament\Widgets;

use App\Models\CompanySetting;
use App\Models\Project;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DevelopmentCostByProject extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $rate = (float) (CompanySetting::query()->first()?->development_hourly_rate ?? 0);
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $inMonth = fn (Builder $q) => $q->whereBetween('committed_at', [$from, $to]);

        return $table
            ->heading('Development cost - ' . now()->format('F Y'))
            ->description($rate > 0
                ? 'Hourly rate: LKR ' . number_format($rate, 2)
                : 'Set the development hourly rate in Company Settings to see costs.')
            ->query(
                Project::query()
                    ->whereNotNull('github_repository_id')
                    ->withCount(['commits as month_commits' => $inMonth])
                    ->withSum(['commits as month_hours' => $inMonth], 'hours')
                    ->orderByDesc('month_hours')
            )
            ->columns([
                TextColumn::make('name')->label('Project')->searchable(),

                TextColumn::make('customer.name')->label('Customer')->placeholder('-'),

                TextColumn::make('month_commits')->label('Commits'),

                TextColumn::make('month_hours')
                    ->label('Hours')
                    ->state(fn (Project $r) => (float) $r->month_hours)
                    ->numeric(2),

                TextColumn::make('cost')
                    ->label('Development cost')
                    ->state(fn (Project $r) => (float) $r->month_hours * $rate)
                    ->money('LKR')
                    ->weight('bold'),
            ])
            ->emptyStateHeading('No projects linked to a Git repository')
            ->emptyStateDescription('Add a Git Repository URL to a project to track its development cost.')
            ->paginated(false);
    }
}
