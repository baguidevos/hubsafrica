<?php

namespace App\Filament\Sae\Pages;

use App\AccompanimentTemplates\AccompanimentTemplateInterface;
use App\Enums\CohortStatus;
use App\Models\Cohort;
use App\Models\Program;
use App\Models\Resource;
use App\Services\AccompanimentTemplateService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

class AccompanimentTemplateDetailPage extends Page
{
    protected static ?string $slug = 'modeles-accompagnement/{templateKey}';

    protected static bool $shouldRegisterNavigation = false;

    public static function getRelativeRouteName(Panel $panel): string
    {
        return 'accompaniment-template-detail';
    }

    public static function getRouteName(?Panel $panel = null): string
    {
        $panel = $panel ?? filament()->getCurrentPanel() ?? filament()->getPanel('sae');

        return 'filament.'.$panel->getId().'.pages.'.static::getRelativeRouteName($panel);
    }

    protected string $view = 'filament.sae.pages.accompaniment-template-detail-page';

    public string $templateKey = 'startup-loop-africinnov';

    public string $activeTab = 'phases'; // 'overview' | 'phases' | 'startups' | 'programme' | 'evaluation' | 'drive'

    // Formulaire d'ajout rapide inline de lien drive
    public ?string $newDriveTitle = null;

    public ?string $newDriveUrl = null;

    public ?string $newDriveDescription = null;

    public function mount(AccompanimentTemplateService $service, ?string $templateKey = null): void
    {
        $key = $templateKey ?? request()->query('template') ?? 'startup-loop-africinnov';

        if (! $service->get($key)) {
            abort(404, "Modèle d'accompagnement [{$key}] non trouvé.");
        }

        $this->templateKey = $key;
    }

    public function getTemplateProperty(AccompanimentTemplateService $service): AccompanimentTemplateInterface
    {
        return $service->get($this->templateKey);
    }

    public function getTitle(): string|Htmlable
    {
        $service = app(AccompanimentTemplateService::class);
        $template = $service->get($this->templateKey);

        return $template ? "Guide A-Z : {$template->getTitle()}" : 'Guide Méthodologique A-Z';
    }

    /**
     * @return Collection<int, resource>
     */
    public function getCustomDriveResourcesProperty(): Collection
    {
        $sae = filament()->getTenant();

        if (! $sae) {
            return new Collection;
        }

        return Resource::where('sae_id', $sae->id)
            ->where('category', 'TEMPLATE_DRIVE_'.$this->templateKey)
            ->latest()
            ->get();
    }

    public function saveInlineDriveLink(): void
    {
        $this->validate([
            'newDriveTitle' => ['required', 'string', 'max:255'],
            'newDriveUrl' => ['required', 'url'],
            'newDriveDescription' => ['nullable', 'string', 'max:1000'],
        ], [
            'newDriveTitle.required' => 'Le titre du dossier ou document est obligatoire.',
            'newDriveUrl.required' => 'L\'adresse URL Google Drive est obligatoire.',
            'newDriveUrl.url' => 'Veuillez saisir une URL valide (ex: https://drive.google.com/...).',
        ]);

        $sae = filament()->getTenant();

        Resource::create([
            'sae_id' => $sae->id,
            'title' => $this->newDriveTitle,
            'description' => $this->newDriveDescription,
            'type' => 'LIEN_EXTERNE',
            'url' => $this->newDriveUrl,
            'category' => 'TEMPLATE_DRIVE_'.$this->templateKey,
            'author' => auth()->user()?->name ?? 'Coordinateur SAE',
            'visibility' => 'TOUS',
        ]);

        $this->reset(['newDriveTitle', 'newDriveUrl', 'newDriveDescription']);

        Notification::make()
            ->title('Lien Google Drive ajouté avec succès !')
            ->success()
            ->send();
    }

    public function deleteCustomDriveLink(int $id): void
    {
        $sae = filament()->getTenant();

        Resource::where('sae_id', $sae->id)
            ->where('id', $id)
            ->delete();

        Notification::make()
            ->title('Ressource supprimée avec succès')
            ->success()
            ->send();
    }

    public function addDriveLinkAction(): Action
    {
        return Action::make('addDriveLink')
            ->label('Ajouter un lien Google Drive')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->slideOver()
            ->modalHeading('Ajouter une ressource Google Drive / Cloud')
            ->modalDescription('Enregistrez un lien Drive pour votre SAE (canevas, dossiers de promotion, pièces justificatives).')
            ->form([
                TextInput::make('title')
                    ->label('Titre du dossier / document')
                    ->placeholder('Ex: Dossier Drive Partagé — Promotion 2026')
                    ->required()
                    ->maxLength(255),

                TextInput::make('url')
                    ->label('Lien Google Drive / Cloud (URL)')
                    ->placeholder('https://drive.google.com/drive/folders/... ou https://docs.google.com/...')
                    ->url()
                    ->required(),

                Textarea::make('description')
                    ->label('Description & Instructions pour l\'équipe')
                    ->placeholder('Ex: Dossier partagé des livrables et fiches de présence.')
                    ->rows(3),
            ])
            ->action(function (array $data) {
                $sae = filament()->getTenant();

                Resource::create([
                    'sae_id' => $sae->id,
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'type' => 'LIEN_EXTERNE',
                    'url' => $data['url'],
                    'category' => 'TEMPLATE_DRIVE_'.$this->templateKey,
                    'author' => auth()->user()?->name ?? 'Coordinateur SAE',
                    'visibility' => 'TOUS',
                ]);

                Notification::make()
                    ->title('Lien Google Drive enregistré !')
                    ->success()
                    ->body("La ressource « {$data['title']} » a été ajoutée à ce modèle.")
                    ->send();
            });
    }

    public function deployAction(): Action
    {
        return Action::make('deploy')
            ->label('Déployer ce modèle')
            ->icon('heroicon-o-rocket-launch')
            ->color('emerald')
            ->slideOver()
            ->modalHeading(fn () => "Déployer le pack : {$this->template->getTitle()}")
            ->modalDescription('Initialise automatiquement les 6 phases, les livrables startups, les livrables SAEI et les critères de notation.')
            ->fillForm(fn (): array => [
                'template_key' => $this->templateKey,
            ])
            ->form([
                Hidden::make('template_key')
                    ->default(fn () => $this->templateKey),

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
                    ->placeholder("Ex: {$this->template->getTitle()} 2026-2027")
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

                $summary = $service->apply($this->templateKey, $sae, $program, $cohort);

                Notification::make()
                    ->title('Modèle méthodologique déployé avec succès !')
                    ->success()
                    ->body("Généré pour {$program->name} : {$summary['phases']} phases, {$summary['startup_deliverables']} livrables startups, {$summary['programme_deliverables']} livrables SAEI.")
                    ->send();
            });
    }
}
