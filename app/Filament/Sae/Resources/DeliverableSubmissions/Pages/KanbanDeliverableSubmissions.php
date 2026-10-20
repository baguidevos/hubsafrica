<?php

namespace App\Filament\Sae\Resources\DeliverableSubmissions\Pages;

use App\Enums\DeliverableSubmissionStatus;
use App\Filament\Sae\Resources\DeliverableSubmissions\DeliverableSubmissionResource;
use App\Models\DeliverableSubmission;
use EduardoRibeiroDev\FilamentKanban\Board;
use EduardoRibeiroDev\FilamentKanban\BoardColumn;
use EduardoRibeiroDev\FilamentKanban\Enums\CardView;
use EduardoRibeiroDev\FilamentKanban\Resources\Pages\BoardPage;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\SelectFilter;

class KanbanDeliverableSubmissions extends BoardPage
{
    protected static string $resource = DeliverableSubmissionResource::class;

    protected static ?string $title = 'Suivi des Livrables & GED';

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
            ->query(fn () => DeliverableSubmission::query()->with(['definition', 'cohort', 'project', 'entrepreneur']))
            ->statusColumn('status')
            ->columns([
                BoardColumn::make(DeliverableSubmissionStatus::SOUMIS->value)
                    ->label('Déposé / Soumis')
                    ->color('warning')
                    ->icon('heroicon-o-document-arrow-up'),

                BoardColumn::make(DeliverableSubmissionStatus::EN_EVALUATION->value)
                    ->label('En évaluation')
                    ->color('info')
                    ->icon('heroicon-o-magnifying-glass'),

                BoardColumn::make(DeliverableSubmissionStatus::CORRECTION_DEMANDEE->value)
                    ->label('Correction demandée')
                    ->color('amber')
                    ->icon('heroicon-o-arrow-path'),

                BoardColumn::make(DeliverableSubmissionStatus::VALIDE->value)
                    ->label('Validé')
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),

                BoardColumn::make(DeliverableSubmissionStatus::REJETEE->value)
                    ->label('Rejeté')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle'),
            ])
            ->cardView(CardView::Accent)
            ->cardSchema([
                TextEntry::make('definition.name')
                    ->label('Livrable')
                    ->weight('bold')
                    ->color('primary')
                    ->icon('heroicon-m-document-text'),

                TextEntry::make('project.name')
                    ->label('Projet')
                    ->icon('heroicon-m-briefcase')
                    ->placeholder('Livrable transverse'),

                TextEntry::make('entrepreneur.full_name')
                    ->label('Entrepreneur')
                    ->icon('heroicon-m-user')
                    ->color('gray'),

                Grid::make(2)->schema([
                    TextEntry::make('cohort.name')
                        ->badge()
                        ->color('info')
                        ->placeholder('Hors cohorte'),

                    TextEntry::make('version')
                        ->badge()
                        ->color('gray')
                        ->formatStateUsing(fn ($state) => "v{$state}"),
                ]),

                TextEntry::make('submitted_at')
                    ->label('Déposé le')
                    ->dateTime('d/m/Y')
                    ->icon('heroicon-m-calendar')
                    ->color('gray')
                    ->placeholder('Récemment'),
            ])
            ->cardActions([
                EditAction::make()
                    ->url(fn (DeliverableSubmission $record) => DeliverableSubmissionResource::getUrl('edit', ['record' => $record])),
            ])
            ->compactCardActions()
            ->updateStatusUsing(function (DeliverableSubmission $record, string $columnId): void {
                $newStatus = DeliverableSubmissionStatus::tryFrom($columnId);
                if ($newStatus) {
                    $record->status = $newStatus;
                    if (in_array($newStatus, [DeliverableSubmissionStatus::VALIDE, DeliverableSubmissionStatus::VALIDEE])) {
                        $record->validated_at = now();
                        $record->validated_by = auth()->id();
                    }
                    $record->save();
                }
            })
            ->filters([
                SelectFilter::make('cohort_id')
                    ->label('Cohorte')
                    ->relationship('cohort', 'name'),
                SelectFilter::make('project_id')
                    ->label('Projet')
                    ->relationship('project', 'name'),
            ])
            ->deferFilters(false)
            ->cardsPerPage(30);
    }
}
