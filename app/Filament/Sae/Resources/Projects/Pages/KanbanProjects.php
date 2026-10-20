<?php

namespace App\Filament\Sae\Resources\Projects\Pages;

use App\Enums\ProjectStatus;
use App\Filament\Sae\Resources\Projects\ProjectResource;
use App\Models\Level;
use App\Models\Project;
use EduardoRibeiroDev\FilamentKanban\Board;
use EduardoRibeiroDev\FilamentKanban\BoardColumn;
use EduardoRibeiroDev\FilamentKanban\Enums\CardView;
use EduardoRibeiroDev\FilamentKanban\Resources\Pages\BoardPage;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\SelectFilter;

class KanbanProjects extends BoardPage
{
    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Maturité des Projets & Paliers';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label('Vue Liste')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }

    public function board(Board $board): Board
    {
        return $board
            ->query(fn () => Project::query()->with(['entrepreneur', 'currentLevel']))
            ->statusColumn('current_level_id')
            ->columns(function () {
                $tenant = Filament::getTenant();
                $levels = Level::query()
                    ->when($tenant, fn ($q) => $q->where('sae_id', $tenant->id))
                    ->orderBy('order')
                    ->get();

                return $levels->map(function (Level $level) {
                    return BoardColumn::make((string) $level->id)
                        ->label($level->name)
                        ->color($level->color ?? 'info')
                        ->icon('heroicon-o-flag');
                })->all();
            })
            ->cardView(CardView::Accent)
            ->cardSchema([
                TextEntry::make('name')
                    ->label('Startup / Entreprise')
                    ->weight('bold')
                    ->color('primary')
                    ->icon('heroicon-m-briefcase'),

                TextEntry::make('entrepreneur.full_name')
                    ->label('Porteur')
                    ->icon('heroicon-m-user')
                    ->color('gray'),

                Grid::make(2)->schema([
                    TextEntry::make('sector')
                        ->badge()
                        ->color('gray')
                        ->placeholder('Secteur non spécifié'),

                    TextEntry::make('status')
                        ->badge()
                        ->color(fn ($state) => $state?->getColor() ?? 'gray')
                        ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'Inconnu'),
                ]),

                TextEntry::make('current_level_entered_at')
                    ->label('Entrée au palier')
                    ->dateTime('d/m/Y')
                    ->icon('heroicon-m-clock')
                    ->color('gray')
                    ->placeholder('Récemment'),
            ])
            ->cardActions([
                ViewAction::make()
                    ->url(fn (Project $record) => ProjectResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn (Project $record) => ProjectResource::getUrl('edit', ['record' => $record])),
            ])
            ->compactCardActions()
            ->updateStatusUsing(function (Project $record, string|int $columnId): void {
                $levelId = (int) $columnId;
                if ($levelId > 0) {
                    $record->current_level_id = $levelId;
                    $record->save();
                }
            })
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut projet')
                    ->options(ProjectStatus::class),
                SelectFilter::make('sector')
                    ->label('Secteur')
                    ->options(fn () => Project::query()->whereNotNull('sector')->distinct()->pluck('sector', 'sector')->all()),
            ])
            ->deferFilters(false)
            ->cardsPerPage(30);
    }
}
