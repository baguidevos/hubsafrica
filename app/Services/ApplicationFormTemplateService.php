<?php

namespace App\Services;

use App\Enums\FormType;
use App\Models\Cohort;
use App\Models\Form;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ApplicationFormTemplateService
{
    public const STANDARD_TEMPLATE_SLUG = 'candidature-standard-hub-africa';

    /**
     * Récupère ou initialise le Template Standardisé Officiel Hub Africa.
     */
    public function getOrCreateStandardTemplate(): Form
    {
        return Form::withoutGlobalScopes()->updateOrCreate(
            ['slug' => self::STANDARD_TEMPLATE_SLUG],
            [
                'name' => 'Dossier de Candidature Standard Hub Africa',
                'description' => 'Modèle officiel standardisé Hub Africa comprenant l\'état civil porteur, la présentation de la startup, la note de motivation et le pitch deck.',
                'type' => FormType::CANDIDATURE,
                'is_template' => true,
                'is_standard' => true,
                'is_mandatory' => true,
                'is_active' => true,
                'sae_id' => null, // Template universel commun
                'submit_label' => 'Déposer ma Candidature',
                'success_message' => 'Félicitations, votre dossier de candidature a été transmis avec succès au comité de sélection.',
                'settings' => [
                    'mode' => 'wizard',
                    'wizard_progress' => true,
                    'step_navigation' => true,
                ],
                'fields' => $this->getStandardFieldsDefinition(),
            ]
        );
    }

    /**
     * Définition exhaustive des sections et champs du socle commun Hub Africa
     * structurée pour le moteur natif Packstub FormBuilder.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStandardFieldsDefinition(): array
    {
        return [
            // Étape 1 : Profil du Porteur
            [
                'type' => 'section',
                'data' => [
                    'key' => 'profil_porteur',
                    'label' => '1. Profil du Porteur de Projet',
                    'description' => 'Coordonnées personnelles et professionnelles du candidat.',
                    'fields' => [
                        [
                            'type' => 'text',
                            'data' => [
                                'key' => 'first_name',
                                'label' => 'Prénom du porteur de projet',
                                'required' => true,
                                'placeholder' => 'Ex: Amadou',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'data' => [
                                'key' => 'last_name',
                                'label' => 'Nom de famille',
                                'required' => true,
                                'placeholder' => 'Ex: Diallo',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'email',
                            'data' => [
                                'key' => 'email',
                                'label' => 'Adresse email',
                                'required' => true,
                                'placeholder' => 'amadou.diallo@startup.africa',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'phone',
                            'data' => [
                                'key' => 'phone',
                                'label' => 'Numéro de téléphone (WhatsApp)',
                                'required' => true,
                                'placeholder' => '+221 77 000 00 00',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'select',
                            'data' => [
                                'key' => 'gender',
                                'label' => 'Genre',
                                'required' => true,
                                'choices' => [
                                    'M' => 'Homme',
                                    'F' => 'Femme',
                                ],
                                'options' => [
                                    ['value' => 'M', 'label' => 'Homme'],
                                    ['value' => 'F', 'label' => 'Femme'],
                                ],
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'data' => [
                                'key' => 'city',
                                'label' => 'Ville / Pays de résidence',
                                'required' => false,
                                'placeholder' => 'Ex: Dakar, Sénégal',
                                'width' => 'half',
                            ],
                        ],
                    ],
                ],
            ],

            // Étape 2 : Projet & Startup
            [
                'type' => 'section',
                'data' => [
                    'key' => 'startup_projet',
                    'label' => '2. Projet & Startup',
                    'description' => 'Présentation de votre entreprise, de son secteur et de sa maturité.',
                    'fields' => [
                        [
                            'type' => 'text',
                            'data' => [
                                'key' => 'project_name',
                                'label' => 'Nom du projet ou de la startup',
                                'required' => true,
                                'placeholder' => 'Ex: AgriSolar Tech',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'data' => [
                                'key' => 'sector',
                                'label' => 'Secteur d\'activité principal',
                                'required' => true,
                                'placeholder' => 'Ex: AgTech, FinTech, Santé, Énergie...',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'number',
                            'data' => [
                                'key' => 'team_size',
                                'label' => 'Taille de l\'équipe (nombre de fondateurs et employés)',
                                'required' => false,
                                'placeholder' => '1',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'textarea',
                            'data' => [
                                'key' => 'pitch',
                                'label' => 'Pitch synthétique du projet (Problème / Solution / Valeur)',
                                'required' => true,
                                'placeholder' => 'Décrivez votre solution en quelques lignes...',
                                'rows' => 4,
                            ],
                        ],
                        [
                            'type' => 'url',
                            'data' => [
                                'key' => 'website',
                                'label' => 'Site internet ou lien vers un prototype',
                                'required' => false,
                                'placeholder' => 'https://monprojet.africa',
                            ],
                        ],
                    ],
                ],
            ],

            // Étape 3 : Motivation & Pièces Jointes
            [
                'type' => 'section',
                'data' => [
                    'key' => 'motivation_pieces',
                    'label' => '3. Motivation & Pièces Jointes',
                    'description' => 'Vos attentes envers le programme et les documents d\'évaluation.',
                    'fields' => [
                        [
                            'type' => 'textarea',
                            'data' => [
                                'key' => 'motivation',
                                'label' => 'Motivation pour intégrer ce programme d\'accompagnement',
                                'required' => true,
                                'placeholder' => 'Expliquez pourquoi vous candidatez et ce que vous attendez des mentors...',
                                'rows' => 4,
                            ],
                        ],
                        [
                            'type' => 'checkboxes',
                            'data' => [
                                'key' => 'needs',
                                'label' => 'Besoins d\'accompagnement prioritaires',
                                'choices' => [
                                    'financement' => 'Levée de fonds & Financement',
                                    'marches' => 'Accès aux marchés & Ventes',
                                    'produit' => 'Développement Produit & Tech',
                                    'mentoring' => 'Mentorat stratégique & Gouvernance',
                                    'juridique' => 'Structuration juridique & Fiscale',
                                ],
                                'required' => false,
                            ],
                        ],
                        [
                            'type' => 'file',
                            'data' => [
                                'key' => 'pitch_deck',
                                'label' => 'Pitch Deck ou présentation du projet (PDF / PPTX, max 15 Mo)',
                                'required' => false,
                                'accept' => 'pdf, pptx, ppt',
                                'max_kb' => 15360,
                                'multiple' => false,
                            ],
                        ],
                        [
                            'type' => 'hidden',
                            'data' => [
                                'key' => 'program_id',
                                'label' => 'Programme ID',
                            ],
                        ],
                        [
                            'type' => 'hidden',
                            'data' => [
                                'key' => 'cohort_id',
                                'label' => 'Cohorte ID',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Retourne la liste à plat de tous les champs standards (dépliés des sections).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFlattenedStandardFields(): array
    {
        $flattened = [];
        foreach ($this->getStandardFieldsDefinition() as $item) {
            if (($item['type'] ?? null) === 'section') {
                foreach ($item['data']['fields'] ?? [] as $subField) {
                    $flattened[] = $subField;
                }
            } else {
                $flattened[] = $item;
            }
        }

        return $flattened;
    }

    /**
     * Récupère tous les templates de candidature disponibles pour une SAE :
     * 1. Le template officiel standardisé Hub Africa.
     * 2. Les templates personnalisés créés par cette SAE.
     */
    public function getAvailableTemplatesForSae(?int $saeId): Collection
    {
        $standard = $this->getOrCreateStandardTemplate();

        $query = Form::withoutGlobalScopes()
            ->where(function ($q) use ($saeId) {
                $q->where('is_standard', true)
                    ->orWhere(function ($sub) use ($saeId) {
                        if ($saeId) {
                            $sub->where('sae_id', $saeId)
                                ->where(function ($typeQ) {
                                    $typeQ->where('type', FormType::CANDIDATURE)
                                        ->orWhere('is_template', true);
                                });
                        }
                    });
            })
            ->where('is_active', true)
            ->orderByRaw('is_standard DESC, name ASC');

        return $query->get();
    }

    /**
     * Duplique un template (standard ou existant) au profit d'une SAE spécifique
     * pour qu'elle puisse le personnaliser librement.
     */
    public function duplicateAsSaeTemplate(Form $sourceTemplate, int $saeId, ?string $newName = null): Form
    {
        $baseName = $newName ?: "{$sourceTemplate->name} (Personnalisé)";
        $slug = Str::slug($baseName).'-'.Str::random(6);

        return Form::withoutGlobalScopes()->create([
            'sae_id' => $saeId,
            'program_id' => $sourceTemplate->program_id,
            'name' => $baseName,
            'slug' => $slug,
            'description' => $sourceTemplate->description,
            'type' => FormType::CANDIDATURE,
            'is_template' => true,
            'is_standard' => false,
            'is_mandatory' => true,
            'is_active' => true,
            'submit_label' => $sourceTemplate->submit_label ?: 'Déposer ma Candidature',
            'success_message' => $sourceTemplate->success_message,
            'fields' => $sourceTemplate->fields,
        ]);
    }

    /**
     * Obtient le formulaire effectif pour une cohorte :
     * Le formulaire assigné ou, à défaut, le template standardisé Hub Africa.
     */
    public function getEffectiveFormForCohort(Cohort $cohort): Form
    {
        if ($cohort->application_form_id) {
            $form = Form::withoutGlobalScopes()->find($cohort->application_form_id);
            if ($form) {
                return $form;
            }
        }

        return $this->getOrCreateStandardTemplate();
    }
}
