<?php

namespace App\Filament\Sae\Pages;

use App\Enums\CohortStatus;
use App\Models\Cohort;
use App\Models\Program;
use App\Services\AccompanimentTemplateService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AccompanimentTemplatesPage extends Page
{
    protected string $view = 'filament.sae.pages.accompaniment-templates-page';

    protected static string|\UnitEnum|null $navigationGroup = 'Pilotage';

    protected static ?string $navigationLabel = 'Modèles & Guides';

    protected static ?string $title = 'Bibliothèque de Modèles d\'Accompagnement';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 3;

    public function getTemplatesProperty(AccompanimentTemplateService $service): array
    {
        return $service->all();
    }

    public function deployAction(): Action
    {
        return Action::make('deploy')
            ->label('Déployer ce modèle')
            ->icon('heroicon-o-rocket-launch')
            ->color('emerald')
            ->slideOver()
            ->modalHeading(fn (array $arguments) => 'Déployer le pack : '.($arguments['template_title'] ?? 'Modèle'))
            ->modalDescription('Initialise automatiquement les 6 phases, les livrables startups, les livrables SAEI et les critères de notation.')
            ->fillForm(fn (array $arguments): array => [
                'template_key' => $arguments['template_key'] ?? 'cube-saei-kpital',
            ])
            ->form([
                Hidden::make('template_key')
                    ->default('cube-saei-kpital'),

                Radio::make('program_mode')
                    ->label('Programme de destination')
                    ->options([
                        'new' => 'Créer un nouveau programme',
                        'existing' => 'Rattacher à un programme existant',
                    ])
                    ->default('new')
                    ->live(),

                TextInput::make('program_name')
                    ->label('Intitulé du programme')
                    ->placeholder('Ex: Startup Loop Togo 2026-2027')
                    ->visible(fn ($get) => $get('program_mode') === 'new')
                    ->required(fn ($get) => $get('program_mode') === 'new'),

                Select::make('existing_program_id')
                    ->label('Programme existant')
                    ->options(fn () => Program::pluck('name', 'id'))
                    ->visible(fn ($get) => $get('program_mode') === 'existing')
                    ->required(fn ($get) => $get('program_mode') === 'existing'),

                TextInput::make('cohort_name')
                    ->label('Créer une cohorte initiale (Optionnel)')
                    ->placeholder('Ex: Promotion Pilote 2026'),

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
                    ->title('Modèle méthodologique déployé avec succès !')
                    ->success()
                    ->body("Généré pour {$program->name} : {$summary['phases']} phases, {$summary['startup_deliverables']} livrables startups, {$summary['programme_deliverables']} livrables SAEI.")
                    ->send();
            });
    }
}
