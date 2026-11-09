<?php

namespace App\Filament\Sae\Resources\Programs\RelationManagers;

use App\Enums\CohortStatus;
use App\Filament\Sae\Resources\Applications\ApplicationResource;
use App\Filament\Sae\Resources\Cohorts\CohortResource;
use App\Models\Cohort;
use App\Models\Form;
use App\Services\ApplicationFormTemplateService;
use App\Services\ProgramEvaluationPresenter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CohortsRelationManager extends RelationManager
{
    protected static string $relationship = 'cohorts';

    protected static ?string $title = 'Cohortes & Promotions';

    protected static string|BackedEnum|null $icon = 'heroicon-o-users';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Nom de la cohorte / promotion')
                        ->placeholder('Ex: Promotion 2026 - Cohorte 1')
                        ->required(),

                    TextInput::make('number')
                        ->label('Numéro séquentiel')
                        ->numeric()
                        ->prefix('#')
                        ->placeholder('1'),

                    Select::make('status')
                        ->label('Statut de la cohorte')
                        ->options(CohortStatus::class)
                        ->default(CohortStatus::OUVERTE)
                        ->required(),

                    TextInput::make('capacity')
                        ->label('Capacité maximale')
                        ->numeric()
                        ->suffix('startups')
                        ->placeholder('20'),

                    DatePicker::make('start_date')
                        ->label('Date de lancement'),

                    DatePicker::make('end_date')
                        ->label('Date de clôture'),
                ]),

                Select::make('application_form_id')
                    ->label('Template du formulaire de candidature')
                    ->options(fn (): array => $this->resolveAvailableFormTemplates())
                    ->default(fn (): int => app(ApplicationFormTemplateService::class)->getOrCreateStandardTemplate()->id)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),

                Textarea::make('objectives')
                    ->label('Objectifs & Thématiques spécifiques')
                    ->rows(2)
                    ->placeholder('Axes stratégiques prioritaires pour cette cohorte...')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn ($query) => $query->withCount(['applications', 'enrollments']))
            ->recordUrl(fn (Cohort $cohort): string => CohortResource::getUrl('view', ['record' => $cohort]))
            ->columns([
                TextColumn::make('number')
                    ->label('#')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state): string => $state ? "#{$state}" : '—')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Cohorte / Promotion')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('applications_count')
                    ->label('Candidatures')
                    ->badge()
                    ->color('primary')
                    ->suffix(' dossier(s)')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('occupancy')
                    ->label('Startups admises / Capacité')
                    ->badge()
                    ->color(fn (Cohort $cohort): string => ProgramEvaluationPresenter::determineOccupancyColor(
                        $cohort->enrollments_count ?? 0,
                        $cohort->capacity
                    ))
                    ->getStateUsing(fn (Cohort $cohort): string => ProgramEvaluationPresenter::formatOccupancyRate(
                        $cohort->enrollments_count ?? 0,
                        $cohort->capacity
                    ))
                    ->alignCenter(),

                TextColumn::make('start_date')
                    ->label('Lancement')
                    ->date('d/m/Y')
                    ->placeholder('Non défini')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Clôture')
                    ->date('d/m/Y')
                    ->placeholder('Non définie')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut de la cohorte')
                    ->options(CohortStatus::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nouvelle cohorte')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $formValues): array {
                        $formValues['sae_id'] = $this->getOwnerRecord()->sae_id;

                        return $formValues;
                    }),
            ])
            ->recordActions([
                Action::make('inspectCohort')
                    ->label('Détails & Candidatures')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Cohort $cohort): string => CohortResource::getUrl('view', ['record' => $cohort])),

                Action::make('filterAllApplications')
                    ->label('Filtrer candidatures')
                    ->icon('heroicon-o-funnel')
                    ->color('info')
                    ->url(fn (Cohort $cohort): string => ApplicationResource::getUrl('index', [
                        'tableFilters' => [
                            'program_id' => ['value' => $cohort->program_id],
                            'cohort_id' => ['value' => $cohort->id],
                        ],
                    ])),

                Action::make('copyJoinLink')
                    ->label('Lien direct')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->action(function ($livewire, Cohort $cohort): void {
                        $joinUrl = route('public.cohort.join.clean', $cohort->id);
                        $livewire->js("window.navigator.clipboard.writeText('{$joinUrl}')");

                        Notification::make()
                            ->title('Lien copié !')
                            ->body("Le lien de candidature pour {$cohort->name} a été copié.")
                            ->success()
                            ->send();
                    }),

                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->defaultSort('start_date', 'desc');
    }

    /**
     * @return array<int, string>
     */
    private function resolveAvailableFormTemplates(): array
    {
        $tenantId = Filament::getTenant()?->id ?? $this->getOwnerRecord()->sae_id;
        $templateService = app(ApplicationFormTemplateService::class);
        $availableTemplates = $templateService->getAvailableTemplatesForSae($tenantId);

        return $availableTemplates->mapWithKeys(function (Form $formTemplate): array {
            $originLabel = $formTemplate->is_standard ? '⭐️ Standard Hubs Africa' : '🏛️ Template SAE';

            return [$formTemplate->id => "{$formTemplate->name} ({$originLabel})"];
        })->all();
    }
}
