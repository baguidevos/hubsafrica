<?php

namespace App\Filament\Sae\Resources\Applications\Pages;

use App\Enums\ApplicationStatus;
use App\Filament\Sae\Resources\Applications\ApplicationResource;
use App\Models\Application;
use EduardoRibeiroDev\FilamentKanban\Board;
use EduardoRibeiroDev\FilamentKanban\BoardColumn;
use EduardoRibeiroDev\FilamentKanban\Enums\CardView;
use EduardoRibeiroDev\FilamentKanban\Resources\Pages\BoardPage;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\SelectFilter;

class KanbanApplications extends BoardPage
{
    protected static string $resource = ApplicationResource::class;

    protected static ?string $title = 'Pipeline des Candidatures';

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
            ->query(fn () => Application::query()->with(['entrepreneur', 'project', 'program', 'cohort']))
            ->statusColumn('status')
            ->columns([
                BoardColumn::make(ApplicationStatus::SOUMISE->value)
                    ->label(ApplicationStatus::SOUMISE->getLabel())
                    ->color(ApplicationStatus::SOUMISE->getColor())
                    ->icon(ApplicationStatus::SOUMISE->getIcon()),

                BoardColumn::make(ApplicationStatus::EN_EXAMEN->value)
                    ->label(ApplicationStatus::EN_EXAMEN->getLabel())
                    ->color(ApplicationStatus::EN_EXAMEN->getColor())
                    ->icon(ApplicationStatus::EN_EXAMEN->getIcon()),

                BoardColumn::make(ApplicationStatus::EN_ATTENTE->value)
                    ->label(ApplicationStatus::EN_ATTENTE->getLabel())
                    ->color(ApplicationStatus::EN_ATTENTE->getColor())
                    ->icon(ApplicationStatus::EN_ATTENTE->getIcon()),

                BoardColumn::make(ApplicationStatus::ACCEPTEE->value)
                    ->label(ApplicationStatus::ACCEPTEE->getLabel())
                    ->color(ApplicationStatus::ACCEPTEE->getColor())
                    ->icon(ApplicationStatus::ACCEPTEE->getIcon()),

                BoardColumn::make(ApplicationStatus::REORIENTEE->value)
                    ->label(ApplicationStatus::REORIENTEE->getLabel())
                    ->color(ApplicationStatus::REORIENTEE->getColor())
                    ->icon(ApplicationStatus::REORIENTEE->getIcon()),

                BoardColumn::make(ApplicationStatus::REFUSEE->value)
                    ->label(ApplicationStatus::REFUSEE->getLabel())
                    ->color(ApplicationStatus::REFUSEE->getColor())
                    ->icon(ApplicationStatus::REFUSEE->getIcon()),
            ])
            ->cardView(CardView::Accent)
            ->cardSchema([
                TextEntry::make('project.name')
                    ->label('Projet')
                    ->weight('bold')
                    ->color('primary')
                    ->icon('heroicon-m-briefcase')
                    ->placeholder(fn (Application $record) => $record->entrepreneur?->full_name ?? 'Candidature'),

                TextEntry::make('entrepreneur.full_name')
                    ->label('Candidat')
                    ->icon('heroicon-m-user')
                    ->color('gray'),

                Grid::make(2)->schema([
                    TextEntry::make('cohort.name')
                        ->badge()
                        ->color('info')
                        ->placeholder('Hors cohorte'),

                    TextEntry::make('score')
                        ->badge()
                        ->color(fn ($state) => match (true) {
                            $state === null => 'gray',
                            $state >= 75 => 'success',
                            $state >= 50 => 'warning',
                            default => 'danger',
                        })
                        ->formatStateUsing(fn ($state) => $state !== null ? "{$state} / 100" : 'Non noté'),
                ]),

                TextEntry::make('submitted_at')
                    ->label('Déposée le')
                    ->dateTime('d/m/Y')
                    ->icon('heroicon-m-calendar')
                    ->color('gray'),
            ])
            ->cardActions([
                ViewAction::make()
                    ->url(fn (Application $record) => ApplicationResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn (Application $record) => ApplicationResource::getUrl('edit', ['record' => $record])),
            ])
            ->compactCardActions()
            ->updateStatusUsing(function (Application $record, string $columnId): void {
                $newStatus = ApplicationStatus::tryFrom($columnId);
                if (! $newStatus) {
                    return;
                }

                $record->status = $newStatus;

                if (! $record->reviewed_at && in_array($newStatus, [
                    ApplicationStatus::EN_EXAMEN,
                    ApplicationStatus::ACCEPTEE,
                    ApplicationStatus::REFUSEE,
                    ApplicationStatus::EN_ATTENTE,
                    ApplicationStatus::REORIENTEE,
                ])) {
                    $record->reviewed_at = now();
                    $record->reviewed_by = auth()->id();
                }

                $record->save();
            })
            ->filters([
                SelectFilter::make('cohort_id')
                    ->label('Cohorte')
                    ->relationship('cohort', 'name'),
                SelectFilter::make('program_id')
                    ->label('Programme')
                    ->relationship('program', 'name'),
            ])
            ->deferFilters(false)
            ->cardsPerPage(30);
    }
}
