<?php

namespace App\Filament\Sae\Pages;

use App\Enums\CohortStatus;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Level;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class AiCopilotPage extends Page
{
    protected string $view = 'filament.sae.pages.ai-copilot-page';

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Copilote IA (Generative UI)';

    protected static ?string $title = 'Copilote IA — Interfaces Génératives';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 11;

    public string $prompt = '';

    /**
     * Carte interactive active : 'none' | 'program_creator' | 'cohort_creator' | 'metrics_dashboard'
     */
    public string $activeWidget = 'metrics_dashboard';

    public string $programName = '';

    public string $programDescription = '';

    public string $programDuration = '6 mois';

    public int $programAdmissionThreshold = 60;

    public ?int $programLevelId = null;

    public bool $programIsPublished = false;

    public ?int $cohortProgramId = null;

    public string $cohortName = '';

    public int $cohortCapacity = 20;

    public string $cohortStatus = 'OUVERTE';

    public function getActiveTenantProperty(): ?Sae
    {
        return Filament::getTenant();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getLevelsProperty(): array
    {
        $tenant = $this->activeTenant;

        if (! $tenant) {
            return [];
        }

        return Level::query()
            ->where('sae_id', $tenant->id)
            ->orderBy('order')
            ->get(['id', 'name'])
            ->map(fn (Level $level) => ['id' => $level->id, 'name' => $level->name])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getProgramsProperty(): array
    {
        $tenant = $this->activeTenant;

        if (! $tenant) {
            return [];
        }

        return Program::query()
            ->where('sae_id', $tenant->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Program $program) => ['id' => $program->id, 'name' => $program->name])
            ->all();
    }

    /**
     * @return array{programs: int, cohorts: int, projects: int, applications: int}
     */
    public function getMetricsProperty(): array
    {
        $tenant = $this->activeTenant;

        if (! $tenant) {
            return ['programs' => 0, 'cohorts' => 0, 'projects' => 0, 'applications' => 0];
        }

        return [
            'programs' => Program::where('sae_id', $tenant->id)->count(),
            'cohorts' => Cohort::where('sae_id', $tenant->id)->count(),
            'projects' => Project::where('sae_id', $tenant->id)->count(),
            'applications' => Application::where('sae_id', $tenant->id)->count(),
        ];
    }

    public function submitPrompt(?string $preset = null): void
    {
        $input = trim($preset ?? $this->prompt);

        if ($input === '') {
            return;
        }

        $this->prompt = $input;
        $this->activeWidget = $this->resolveWidgetIntent($input);

        if ($this->activeWidget === 'cohort_creator' && ! $this->cohortProgramId) {
            $programs = $this->programs;
            $this->cohortProgramId = $programs[0]['id'] ?? null;
        }
    }

    /**
     * Décision pure : détermine le composant interactif à afficher selon la demande.
     */
    public function resolveWidgetIntent(string $input): string
    {
        $normalized = Str::lower($input);

        if (str_contains($normalized, 'cohorte') || str_contains($normalized, 'promotion')) {
            return 'cohort_creator';
        }

        if (str_contains($normalized, 'programme') || str_contains($normalized, 'créer') || str_contains($normalized, 'creer')) {
            return 'program_creator';
        }

        return 'metrics_dashboard';
    }

    public function createProgram(): void
    {
        $tenant = $this->activeTenant;

        if (! $tenant) {
            return;
        }

        $validated = $this->validate([
            'programName' => 'required|string|max:200',
            'programDescription' => 'required|string',
            'programDuration' => 'nullable|string|max:50',
            'programAdmissionThreshold' => 'nullable|integer|min:0|max:100',
            'programLevelId' => 'nullable|integer',
        ]);

        $program = Program::create([
            'sae_id' => $tenant->id,
            'name' => $validated['programName'],
            'slug' => Str::slug($validated['programName']).'-'.Str::lower(Str::random(5)),
            'description' => $validated['programDescription'],
            'duration' => $validated['programDuration'] ?: '6 mois',
            'admission_threshold' => $validated['programAdmissionThreshold'] ?? 60,
            'level_id' => $validated['programLevelId'] ?: null,
            'is_published' => $this->programIsPublished,
        ]);

        $this->reset(['programName', 'programDescription', 'programLevelId', 'programIsPublished']);
        $this->programDuration = '6 mois';
        $this->programAdmissionThreshold = 60;
        $this->activeWidget = 'metrics_dashboard';

        Notification::make()
            ->title('Programme créé')
            ->body("Le programme « {$program->name} » a été créé avec succès.")
            ->success()
            ->send();
    }

    public function createCohort(): void
    {
        $tenant = $this->activeTenant;

        if (! $tenant) {
            return;
        }

        $validated = $this->validate([
            'cohortProgramId' => 'required|integer',
            'cohortName' => 'required|string|max:150',
            'cohortCapacity' => 'required|integer|min:1|max:500',
            'cohortStatus' => 'required|string|in:OUVERTE,EN_COURS,CLOTUREE',
        ]);

        $program = Program::where('sae_id', $tenant->id)->find($validated['cohortProgramId']);

        if (! $program) {
            Notification::make()
                ->title('Programme introuvable')
                ->danger()
                ->send();

            return;
        }

        $cohort = Cohort::create([
            'sae_id' => $tenant->id,
            'program_id' => $program->id,
            'name' => $validated['cohortName'],
            'slug' => Str::slug($validated['cohortName']).'-'.Str::lower(Str::random(5)),
            'number' => Cohort::where('program_id', $program->id)->count() + 1,
            'capacity' => $validated['cohortCapacity'],
            'status' => CohortStatus::from($validated['cohortStatus']),
        ]);

        $this->reset(['cohortName']);
        $this->cohortCapacity = 20;
        $this->cohortStatus = 'OUVERTE';
        $this->activeWidget = 'metrics_dashboard';

        Notification::make()
            ->title('Cohorte créée')
            ->body("La cohorte « {$cohort->name} » a été créée dans « {$program->name} ».")
            ->success()
            ->send();
    }
}
