<?php

namespace App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Schemas;

use App\Enums\DeliverableSubmissionStatus;
use App\Models\Enrollment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliverableSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('entrepreneur_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->id),

                Hidden::make('sae_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->sae_id),

                Hidden::make('submitted_by_id')
                    ->default(fn () => auth()->id()),

                Hidden::make('status')
                    ->default(DeliverableSubmissionStatus::SOUMIS),

                Hidden::make('submitted_at')
                    ->default(now()),

                Section::make('Dépôt de votre livrable')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('project_id')
                                ->label('Projet concerné')
                                ->relationship('project', 'name', modifyQueryUsing: function ($query) {
                                    $entrepreneur = auth()->user()?->entrepreneurProfile;
                                    if ($entrepreneur) {
                                        $query->where('entrepreneur_id', $entrepreneur->id);
                                    }
                                })
                                ->required(),

                            Select::make('definition_id')
                                ->label('Livrable attendu')
                                ->relationship('definition', 'name', modifyQueryUsing: function ($query) {
                                    $saeId = auth()->user()?->entrepreneurProfile?->sae_id;
                                    if ($saeId) {
                                        $query->where('sae_id', $saeId);
                                    }
                                })
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('cohort_phase_id')
                                ->label('Jalon pédagogique / Phase')
                                ->relationship('cohortPhase', 'name', modifyQueryUsing: function ($query) {
                                    $entrepreneur = auth()->user()?->entrepreneurProfile;
                                    if ($entrepreneur) {
                                        $cohortIds = Enrollment::where('entrepreneur_id', $entrepreneur->id)
                                            ->pluck('cohort_id');
                                        if ($cohortIds->isNotEmpty()) {
                                            $query->whereIn('cohort_id', $cohortIds);
                                        }
                                    }
                                })
                                ->searchable()
                                ->preload()
                                ->helperText('Optionnel : associez ce dépôt à un jalon spécifique.'),

                            TextInput::make('version')
                                ->label('Version')
                                ->numeric()
                                ->default(1)
                                ->prefix('v')
                                ->required(),

                            TextInput::make('file_name')
                                ->label('Titre du document')
                                ->placeholder('Ex: BMC_version_finale.pdf')
                                ->required(),
                        ]),

                        FileUpload::make('file_path')
                            ->label('Fichier du livrable (PDF, Word, Excel, Slide...)')
                            ->disk('public')
                            ->directory('deliverables')
                            ->openable()
                            ->downloadable()
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('comment')
                            ->label('Message / Commentaires pour votre coach')
                            ->rows(3)
                            ->placeholder('Précisez les avancées notables ou les questions spécifiques sur ce livrable...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Évaluation & Retour du Coach')
                    ->visible(fn ($record) => $record && $record->evaluations()->exists())
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('eval_score')
                                ->label('Note attribuée')
                                ->default(fn ($record) => $record?->latestEvaluation?->score !== null ? $record->latestEvaluation->score.' / 100' : 'Non noté')
                                ->disabled(),

                            TextInput::make('eval_status')
                                ->label('Décision du coach')
                                ->default(fn ($record) => match ($record?->latestEvaluation?->status) {
                                    'VALIDE' => 'Validé avec succès',
                                    'A_REVOIR' => 'À retravailler',
                                    'REJETE' => 'Non conforme',
                                    default => $record?->latestEvaluation?->status ?? 'En attente',
                                })
                                ->disabled(),

                            TextInput::make('eval_date')
                                ->label('Évalué le')
                                ->default(fn ($record) => $record?->latestEvaluation?->evaluated_at?->format('d/m/Y H:i') ?? '—')
                                ->disabled(),
                        ]),

                        Textarea::make('eval_feedback')
                            ->label('Commentaires & Conseils du coach')
                            ->default(fn ($record) => $record?->latestEvaluation?->feedback ?? 'Aucun commentaire.')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
