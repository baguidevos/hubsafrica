<?php

namespace App\Filament\Sae\Resources\Programs\Pages;

use App\Enums\CohortStatus;
use App\Filament\Sae\Resources\Programs\ProgramResource;
use App\Models\Cohort;
use App\Models\Program;
use App\Services\AccompanimentTemplateService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPrograms extends ListRecords
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('applyTemplate')
                ->label('Modèle d\'accompagnement')
                ->icon('heroicon-o-sparkles')
                ->color('emerald')
                ->slideOver()
                ->modalHeading('Déployer un pack méthodologique complet')
                ->modalDescription('Initialise automatiquement toutes les phases, livrables startups, livrables SAEI et critères de jury du modèle sélectionné.')
                ->form([
                    Select::make('template_key')
                        ->label('Modèle méthodologique')
                        ->options(fn (AccompanimentTemplateService $service) => $service->asSelectOptions())
                        ->default('startup-loop-africinnov')
                        ->required(),

                    Radio::make('program_mode')
                        ->label('Programme cible')
                        ->options([
                            'new' => 'Créer un nouveau programme',
                            'existing' => 'Appliquer à un programme existant',
                        ])
                        ->default('new')
                        ->live(),

                    TextInput::make('program_name')
                        ->label('Intitulé du nouveau programme')
                        ->placeholder('Ex: Startup Loop Togo 2026')
                        ->visible(fn ($get) => $get('program_mode') === 'new')
                        ->required(fn ($get) => $get('program_mode') === 'new'),

                    Select::make('existing_program_id')
                        ->label('Sélectionner le programme')
                        ->options(fn () => Program::pluck('name', 'id'))
                        ->visible(fn ($get) => $get('program_mode') === 'existing')
                        ->required(fn ($get) => $get('program_mode') === 'existing'),

                    TextInput::make('cohort_name')
                        ->label('Créer une cohorte initiale (Optionnel)')
                        ->placeholder('Ex: Promotion 1 - Pilote 2026'),

                    DatePicker::make('cohort_start_date')
                        ->label('Date de lancement de la cohorte')
                        ->default(now()),
                ])
                ->action(function (array $data, AccompanimentTemplateService $service) {
                    $sae = filament()->getTenant();

                    if ($data['program_mode'] === 'new') {
                        $program = Program::create([
                            'sae_id' => $sae->id,
                            'name' => $data['program_name'],
                            'status' => 'ACTIF',
                        ]);
                    } else {
                        $program = Program::findOrFail($data['existing_program_id']);
                    }

                    $cohort = null;
                    if (! empty($data['cohort_name'])) {
                        $cohort = Cohort::create([
                            'sae_id' => $sae->id,
                            'program_id' => $program->id,
                            'name' => $data['cohort_name'],
                            'status' => CohortStatus::EN_COURS,
                            'start_date' => $data['cohort_start_date'] ?? now(),
                        ]);
                    }

                    $summary = $service->apply($data['template_key'], $sae, $program, $cohort);

                    Notification::make()
                        ->title('Pack méthodologique déployé avec succès !')
                        ->success()
                        ->body("Généré pour {$program->name} : {$summary['phases']} phases, {$summary['startup_deliverables']} livrables startups, {$summary['programme_deliverables']} livrables SAEI.")
                        ->send();
                }),

            CreateAction::make()->slideOver(),
        ];
    }
}
