<?php

namespace App\Mcp\Tools\Forms;

use App\Enums\FormType;
use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Form;
use App\Models\Level;
use App\Models\Program;
use App\Support\FormDefinitionBuilder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_form')]
#[Title('Créer un formulaire')]
#[Description('Crée un nouveau formulaire/questionnaire (candidature, diagnostic, satisfaction, bilan, collecte) pour la SAE avec ses champs structurés. Si l\'utilisateur n\'a pas encore précisé tous les détails et questions, appelez d\'abord open_form_creator pour lui présenter le formulaire interactif.')]
class CreateFormTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in(array_column(FormType::cases(), 'value'))],
            'description' => 'nullable|string|max:1000',
            'program_id' => 'nullable|integer',
            'target_level_id' => 'nullable|integer',
            'is_mandatory' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'wizard_mode' => 'nullable|boolean',
            'submit_label' => 'nullable|string|max:100',
            'success_message' => 'nullable|string|max:500',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => ['required', 'string', Rule::in(FormDefinitionBuilder::ALLOWED_FIELD_TYPES)],
            'fields.*.required' => 'nullable|boolean',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string|max:150',
        ]);

        if (! empty($validated['program_id'])) {
            $programExists = Program::query()
                ->where('sae_id', $sae->id)
                ->whereKey($validated['program_id'])
                ->exists();

            if (! $programExists) {
                return Response::error("Programme introuvable pour cette SAE (ID : {$validated['program_id']}).");
            }
        }

        if (! empty($validated['target_level_id'])) {
            $levelExists = Level::query()
                ->where('sae_id', $sae->id)
                ->whereKey($validated['target_level_id'])
                ->exists();

            if (! $levelExists) {
                return Response::error("Palier introuvable pour cette SAE (ID : {$validated['target_level_id']}).");
            }
        }

        $sections = FormDefinitionBuilder::buildPackstubSections($validated['fields'], $validated['name']);
        $fieldsCount = count($sections[0]['data']['fields'] ?? []);

        if ($fieldsCount === 0) {
            return Response::error('Le formulaire doit contenir au moins un champ valide avec un libellé.');
        }

        $formType = FormType::from($validated['type']);
        $slug = Str::slug($validated['name']).'-'.Str::lower(Str::random(6));

        $form = Form::create([
            'sae_id' => $sae->id,
            'program_id' => $validated['program_id'] ?? null,
            'target_level_id' => $validated['target_level_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'type' => $formType,
            'is_template' => true,
            'is_mandatory' => (bool) ($validated['is_mandatory'] ?? true),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'store_submissions' => true,
            'submit_label' => trim((string) ($validated['submit_label'] ?? '')) ?: 'Soumettre',
            'success_message' => trim((string) ($validated['success_message'] ?? '')) ?: 'Merci, vos réponses ont bien été enregistrées.',
            'settings' => [
                'mode' => ! empty($validated['wizard_mode']) ? 'wizard' : 'standard',
            ],
            'fields' => $sections,
        ]);

        $this->logAudit(
            action: 'CREATE',
            entity: 'Form',
            entityId: (string) $form->id,
            details: [
                'name' => $form->name,
                'type' => $formType->value,
                'fields_count' => $fieldsCount,
                'is_active' => (bool) $form->is_active,
            ],
        );

        return Response::structured([
            'message' => "Le formulaire « {$form->name} » a été créé avec succès ({$fieldsCount} question(s)).",
            'form' => [
                'id' => $form->id,
                'name' => $form->name,
                'slug' => $form->slug,
                'type' => $formType->value,
                'type_label' => $formType->getLabel(),
                'program_id' => $form->program_id,
                'target_level_id' => $form->target_level_id,
                'fields_count' => $fieldsCount,
                'is_mandatory' => (bool) $form->is_mandatory,
                'is_active' => (bool) $form->is_active,
                'public_url' => url("/forms/{$form->slug}"),
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('Titre du formulaire (ex: Enquête de satisfaction Cohorte 2026)')
                ->required(),
            'type' => $schema->string()
                ->enum(array_column(FormType::cases(), 'value'))
                ->description('Type métier du formulaire (CANDIDATURE, BASELINE, DIAGNOSTIC, SATISFACTION, ENDLINE, POST_PROGRAMME, ABANDON, COLLECTE_DONNEES, AUTRE)')
                ->required(),
            'description' => $schema->string()
                ->description('Courte description affichée en en-tête du formulaire'),
            'program_id' => $schema->integer()
                ->description('ID du programme associé (optionnel)'),
            'target_level_id' => $schema->integer()
                ->description('ID du palier cible (optionnel)'),
            'is_mandatory' => $schema->boolean()
                ->description('Indique si le remplissage est obligatoire pour les entrepreneurs')
                ->default(true),
            'is_active' => $schema->boolean()
                ->description('Activer immédiatement le formulaire (true) ou le garder en brouillon (false)')
                ->default(true),
            'wizard_mode' => $schema->boolean()
                ->description('Afficher le formulaire en mode étapes (wizard)')
                ->default(false),
            'submit_label' => $schema->string()
                ->description('Texte du bouton de soumission (ex: Envoyer mes réponses)'),
            'success_message' => $schema->string()
                ->description('Message affiché après soumission du formulaire'),
            'fields' => $schema->array()
                ->items($schema->object([
                    'label' => $schema->string()->description('Libellé de la question')->required(),
                    'type' => $schema->string()->enum(FormDefinitionBuilder::ALLOWED_FIELD_TYPES)->description('Type du champ')->required(),
                    'required' => $schema->boolean()->description('Champ obligatoire')->default(false),
                    'placeholder' => $schema->string()->description('Texte indicatif (placeholder)'),
                    'options' => $schema->array()->items($schema->string())->description('Choix possibles pour select, radio ou checkboxes'),
                ]))
                ->description('Liste des questions/champs du formulaire (minimum 1)')
                ->required(),
        ];
    }
}
