<?php

namespace App\Services;

use App\Enums\FormType;
use App\Models\Form;
use App\Models\Level;
use App\Models\Sae;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SaeOnboardingService
{
    public function __construct(
        protected ApplicationFormTemplateService $templateService
    ) {}

    /**
     * Initialise tous les prérequis opérationnels d'une nouvelle structure SAE :
     * 1. Formulaire d'inscription & dossier de candidature officiel propre à la structure.
     * 2. Paliers et niveaux de maturité par défaut (Idéation, Incubation, Accélération).
     * 3. Attribution du rôle Shield 'responsable' au propriétaire dans le scope tenant.
     */
    public function initializeSaeDefaults(Sae $sae, ?User $user = null): Form
    {
        // 1. Initialiser le template de candidature / formulaire d'inscription officiel pour cette SAE
        $standardTemplate = $this->templateService->getOrCreateStandardTemplate();

        $formName = "Dossier de Candidature & Inscription — {$sae->name}";

        $saeForm = Form::withoutGlobalScopes()
            ->where('sae_id', $sae->id)
            ->where('type', FormType::CANDIDATURE)
            ->first();

        if (! $saeForm) {
            $saeForm = $this->templateService->duplicateAsSaeTemplate(
                $standardTemplate,
                $sae->id,
                $formName
            );
        }

        // 2. Initialiser les niveaux de maturité par défaut si la SAE n'en possède aucun
        if ($sae->levels()->doesntExist()) {
            Level::create([
                'sae_id' => $sae->id,
                'name' => '1. Idéation',
                'order' => 1,
                'color' => '#f59e0b',
                'description' => 'Validation de l\'idée et proposition de valeur initiale',
                'min_average' => 50.00,
            ]);

            Level::create([
                'sae_id' => $sae->id,
                'name' => '2. Incubation',
                'order' => 2,
                'color' => '#0d9488',
                'description' => 'Développement du prototype (MVP) et validation marché',
                'min_average' => 65.00,
            ]);

            Level::create([
                'sae_id' => $sae->id,
                'name' => '3. Accélération',
                'order' => 3,
                'color' => '#6366f1',
                'description' => 'Croissance commerciale et levée de fonds d\'amorçage',
                'min_average' => 75.00,
            ]);
        }

        // 3. Assigner le rôle Shield 'responsable' au responsable dans le scope team du tenant
        if ($user) {
            setPermissionsTeamId($sae->id);
            $responsableRole = Role::firstOrCreate([
                'name' => 'responsable',
                'guard_name' => 'web',
            ]);
            if (! $user->hasRole($responsableRole)) {
                $user->assignRole($responsableRole);
            }
        }

        return $saeForm;
    }
}
