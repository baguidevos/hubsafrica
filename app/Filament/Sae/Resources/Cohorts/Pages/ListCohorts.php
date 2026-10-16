<?php

namespace App\Filament\Sae\Resources\Cohorts\Pages;

use App\Enums\CohortStatus;
use App\Filament\Sae\Resources\Cohorts\CohortResource;
use App\Models\Cohort;
use App\Models\Program;
use App\Services\AccompanimentTemplateService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCohorts extends ListRecords
{
    protected static string $resource = CohortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('applyTemplate')
                ->label('Modèle d\'accompagnement')
                ->icon('heroicon-o-sparkles')
                ->color('emerald')
                ->slideOver()
                ->modalHeading('Initialiser une cohorte avec un pack méthodologique')
                ->modalDescription('Déploie automatiquement les 6 phases, les livrables startups, les livrables SAEI et synchronise le calendrier de la cohorte.')
                ->form([
                    Select::make('template_key')
                        ->label('Modèle méthodologique')
                        ->options(fn (AccompanimentTemplateService $service) => $service->asSelectOptions())
                        ->default('cube-saei-kpital')
                        ->required(),

                    Select::make('program_id')
                        ->label('Programme de rattachement')
                        ->options(fn () => Program::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),

                    TextInput::make('name')
                        ->label('Nom de la nouvelle promotion / cohorte')
                        ->placeholder('Ex: Promotion Pilote 2026')
                        ->required(),

                    DatePicker::make('start_date')
                        ->label('Date de lancement officiel')
                        ->default(now()),
                ])
                ->action(function (array $data, AccompanimentTemplateService $service) {
                    $sae = filament()->getTenant();
                    $program = Program::findOrFail($data['program_id']);

                    $cohort = Cohort::create([
                        'sae_id' => $sae->id,
                        'program_id' => $program->id,
                        'name' => $data['name'],
                        'status' => CohortStatus::EN_COURS,
                        'start_date' => $data['start_date'] ?? now(),
                    ]);

                    $summary = $service->apply($data['template_key'], $sae, $program, $cohort);

                    Notification::make()
                        ->title('Cohorte & Méthodologie initialisées !')
                        ->success()
                        ->body("La cohorte {$cohort->name} a été initialisée avec {$summary['phases']} jalons synchronisés et {$summary['startup_deliverables']} livrables.")
                        ->send();
                }),

            CreateAction::make()->slideOver(),
        ];
    }
}
