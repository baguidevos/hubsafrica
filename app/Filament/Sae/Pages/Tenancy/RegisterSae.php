<?php

namespace App\Filament\Sae\Pages\Tenancy;

use App\Models\Sae;
use App\Services\SaeOnboardingService;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class RegisterSae extends RegisterTenant
{
    protected string $view = 'filament.sae.pages.register-sae';

    protected Width|string|null $maxWidth = Width::SixExtraLarge;

    public function getMaxWidth(): Width|string|null
    {
        return Width::SixExtraLarge;
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public static function getLabel(): string
    {
        return 'Enregistrer une Structure d\'Accompagnement (SAE)';
    }

    public function mount(): void
    {
        // Règle stricte : Un utilisateur ne peut créer qu'une seule structure SAE
        if (! auth()->user()->canCreateTenants()) {
            $existingSae = auth()->user()->ownedSae ?? auth()->user()->saes()->first();
            if ($existingSae) {
                $this->redirect(filament()->getPanel('sae')->getUrl($existingSae));

                return;
            }
            abort(403, 'Vous possédez déjà une structure d\'accompagnement.');
        }

        parent::mount();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Identité & Coordonnées')
                        ->icon('heroicon-o-building-office-2')
                        ->description('Informations générales et contacts officiels')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')
                                    ->label('Nom de la SAE / Incubateur')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ex: Jokkolabs Dakar, 2iE Incubator')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->label('Identifiant unique (Slug URL)')
                                    ->required()
                                    ->unique(Sae::class, 'slug')
                                    ->maxLength(255)
                                    ->helperText('Identifiant dans l\'URL (ex: sae/{slug})'),

                                TextInput::make('email')
                                    ->label('Email de contact officiel')
                                    ->email()
                                    ->maxLength(255)
                                    ->placeholder('contact@incubateur.org'),

                                TextInput::make('phone')
                                    ->label('Téléphone officiel / WhatsApp')
                                    ->tel()
                                    ->maxLength(50)
                                    ->placeholder('+221 33 800 00 00'),

                                TextInput::make('website')
                                    ->label('Site Web institutionnel (optionnel)')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://www.incubateur.africa'),

                                TextInput::make('logo')
                                    ->label('URL du logo (optionnel)')
                                    ->maxLength(255)
                                    ->placeholder('https://.../logo.png'),
                            ]),

                            Textarea::make('address')
                                ->label('Adresse physique / Ville / Pays')
                                ->rows(2)
                                ->placeholder('Ex: Boulevard de l\'Est, Point E, Dakar, Sénégal')
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Présentation synthétique & Pitch')
                                ->rows(3)
                                ->placeholder('Présentation en quelques phrases de la structure, de sa genèse et de son positionnement.')
                                ->columnSpanFull(),
                        ]),

                    Step::make('Vision & Mission')
                        ->icon('heroicon-o-light-bulb')
                        ->description('Raison d\'être et orientation stratégique')
                        ->schema([
                            RichEditor::make('vision')
                                ->label('Vision Stratégique')
                                ->helperText('Quelle est la vision à long terme de votre structure pour l\'écosystème entrepreneurial ?')
                                ->placeholder('Ex: Devenir le pôle d\'excellence ouest-africain pour les startups vertes à fort potentiel d\'exportation...')
                                ->toolbarButtons([
                                    'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'h2', 'h3', 'blockquote', 'undo', 'redo',
                                ])
                                ->columnSpanFull(),

                            RichEditor::make('mission')
                                ->label('Mission & Raison d\'être')
                                ->helperText('Quelle est la mission opérationnelle auprès des fondateurs et porteurs de projets ?')
                                ->placeholder('Ex: Offrir un accompagnement intensif et sur-mesure couvrant la structuration méthodologique, la modélisation financière et la levée de fonds...')
                                ->toolbarButtons([
                                    'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'h2', 'h3', 'blockquote', 'undo', 'redo',
                                ])
                                ->columnSpanFull(),
                        ]),

                    Step::make('Objectifs & Accompagnement')
                        ->icon('heroicon-o-rocket-launch')
                        ->description('Piliers d\'impact, services et seuils d\'évaluation')
                        ->schema([
                            RichEditor::make('objectives')
                                ->label('Objectifs Prioritaires & Piliers d\'Impact')
                                ->helperText('Quels résultats concrets visez-vous (emplois décents créés, inclusion des femmes et jeunes, financements levés...) ?')
                                ->placeholder('Ex: • Accompagner 50 startups par an • 60% d\'équipes dirigées par des femmes • 1,5M$ de financements mobilisés...')
                                ->toolbarButtons([
                                    'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'h2', 'h3', 'blockquote', 'undo', 'redo',
                                ])
                                ->columnSpanFull(),

                            RichEditor::make('services')
                                ->label('Offre d\'Accompagnement & Services Clés')
                                ->helperText('Ateliers méthodologiques, coaching 1-on-1, Data Room investisseurs, FabLab, mise en relation...')
                                ->placeholder('Ex: Coaching hebdomadaire, diagnostic initial 360°, mise en relation avec des bailleurs, préparation aux jurys...')
                                ->toolbarButtons([
                                    'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'h2', 'h3', 'blockquote', 'undo', 'redo',
                                ])
                                ->columnSpanFull(),

                            Grid::make(2)->schema([
                                TextInput::make('default_admission_threshold')
                                    ->label('Seuil d\'admission directe par défaut (/100)')
                                    ->helperText('Note minimale requise par défaut pour qu\'un dossier soit directement admis lors des jurys.')
                                    ->default(75)
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100),

                                TextInput::make('default_waitlist_threshold')
                                    ->label('Seuil de liste d\'attente par défaut (/100)')
                                    ->helperText('Note minimale par défaut pour la liste d\'attente. En-dessous de ce seuil, le dossier est rejeté.')
                                    ->default(50)
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100),
                            ]),
                        ]),
                ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            size="md"
                            color="primary"
                            icon="heroicon-o-check-circle"
                        >
                            Finaliser l'enregistrement de la structure
                        </x-filament::button>
                    BLADE)))
                    ->skippable(false),
            ]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function handleRegistration(array $data): Sae
    {
        $user = auth()->user();

        if (! $user->canCreateTenants()) {
            abort(403, 'Vous ne pouvez créer qu\'une seule structure d\'accompagnement.');
        }

        $data['owner_id'] = $user->id;
        $data['status'] = 'ACTIVE';

        $sae = Sae::create($data);

        // Rattachement de l'utilisateur comme RESPONSABLE de la SAE
        $user->saes()->attach($sae->id, [
            'role' => 'RESPONSABLE',
            'status' => 'ACTIVE',
        ]);

        // Initialisation automatique : Formulaire de candidature propre à la SAE, Paliers & Rôles
        app(SaeOnboardingService::class)->initializeSaeDefaults($sae, $user);

        return $sae;
    }
}
