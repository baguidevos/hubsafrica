<?php

use App\Enums\ApplicationEvaluationStatus;
use App\Enums\ApplicationStatus;
use App\Enums\CohortStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Mcp\Tools\Applications\EvaluateApplicationByGridTool;
use App\Mcp\Tools\Applications\ExportApplicationEvaluationsSheetTool;
use App\Mcp\Tools\Applications\GetApplicationDetailsTool;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\EvaluationCriterion;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use App\Services\ApplicationGridEvaluationService;
use Illuminate\Support\Facades\Storage;
use Laravel\Mcp\Request as McpRequest;

test('ApplicationGridEvaluationService calculates weighted score and recommends outcome without applying decisions', function () {
    $service = new ApplicationGridEvaluationService;

    $weighted = $service->calculateWeightedScore([
        ['score' => 18, 'max_score' => 20, 'weight' => 40], // 90% * 40 = 36
        ['score' => 12, 'max_score' => 20, 'weight' => 60], // 60% * 60 = 36
    ]);

    expect($weighted)->toBe(72.0);
    expect($service->recommendOutcome(82.0, 75, 50)['code'])->toBe('REUSSI');
    expect($service->recommendOutcome(65.0, 75, 50)['code'])->toBe('LISTE_ATTENTE');
    expect($service->recommendOutcome(40.0, 75, 50)['code'])->toBe('RECALE');
    expect($service->recommendOutcome(null, 75, 50)['code'])->toBe('A_EVALUER');
});

test('evaluate_application_by_grid saves criterion scores as draft without changing application status or enrolling', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Jury Humain',
        'slug' => 'sae-jury-humain-'.uniqid(),
    ]);
    app()->instance('current_sae', $sae);
    $this->actingAs($owner);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'AgriPulse',
        'status' => ProgramStatus::ACTIF,
        'admission_threshold' => 75,
        'waitlist_threshold' => 50,
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte 1',
        'status' => CohortStatus::OUVERTE,
    ]);

    $criterionInnovation = EvaluationCriterion::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Innovation',
        'weight' => 50,
        'max_score' => 20,
        'position' => 1,
    ]);

    $criterionMarket = EvaluationCriterion::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Potentiel Marché',
        'weight' => 50,
        'max_score' => 20,
        'position' => 2,
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Awa',
        'last_name' => 'Koné',
        'email' => 'awa.kone@example.com',
        'status' => EntrepreneurStatus::CANDIDAT,
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Terroir Plus',
        'sector' => 'Agriculture',
        'status' => ProjectStatus::CANDIDAT,
    ]);

    $application = Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $entrepreneur->id,
        'project_id' => $project->id,
        'status' => ApplicationStatus::SOUMISE,
        'submitted_at' => now(),
    ]);

    // 1. get_application_details expose la grille du programme
    $detailsResponse = app(GetApplicationDetailsTool::class)->handle(
        new McpRequest(['application_id' => $application->id]),
    );
    $detailsPayload = json_decode((string) $detailsResponse->responses()->first()->content(), true);

    expect($detailsPayload['program']['evaluation_grid'])->toHaveCount(2);

    // 2. evaluate_application_by_grid note selon la grille SANS appliquer la décision
    $evalResponse = app(EvaluateApplicationByGridTool::class)->handle(
        new McpRequest([
            'application_id' => $application->id,
            'criteria_scores' => [
                ['criterion_id' => $criterionInnovation->id, 'score' => 18, 'comment' => 'Solution très pertinente'],
                ['criterion_id' => $criterionMarket->id, 'score' => 16, 'comment' => 'Marché local solide'],
            ],
            'summary_comment' => 'Dossier solide à soumettre au jury pour validation.',
        ]),
        app(ApplicationGridEvaluationService::class),
    );

    $evalPayload = json_decode((string) $evalResponse->responses()->first()->content(), true);

    expect($evalPayload['weighted_score'])->toBe(85)
        ->and($evalPayload['recommendation']['code'])->toBe('REUSSI')
        ->and($evalPayload['decision_applied'])->toBeFalse();

    $freshApplication = $application->fresh();
    expect($freshApplication->status)->toBe(ApplicationStatus::SOUMISE)
        ->and($freshApplication->decision)->toBeNull()
        ->and($freshApplication->score)->toBe(85)
        ->and(Enrollment::where('project_id', $project->id)->exists())->toBeFalse();

    $evaluation = ApplicationEvaluation::where('application_id', $application->id)->first();
    expect($evaluation)->not->toBeNull()
        ->and($evaluation->status)->toBe(ApplicationEvaluationStatus::BROUILLON)
        ->and($evaluation->scores()->count())->toBe(2);
});

test('export_application_evaluations_sheet ranks candidates from reussi to recale and generates an excel csv file', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Export Jury',
        'slug' => 'sae-export-jury-'.uniqid(),
    ]);
    app()->instance('current_sae', $sae);
    $this->actingAs($owner);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'AgriPulse Export',
        'status' => ProgramStatus::ACTIF,
        'admission_threshold' => 75,
        'waitlist_threshold' => 50,
    ]);

    $criterion = EvaluationCriterion::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Impact',
        'weight' => 100,
        'max_score' => 20,
        'position' => 1,
    ]);

    $createEvaluatedApplication = function (string $firstName, string $projectName, float $criterionScore, string $comment) use ($sae, $program, $criterion): Application {
        $entrepreneur = Entrepreneur::create([
            'sae_id' => $sae->id,
            'first_name' => $firstName,
            'last_name' => 'Test',
            'email' => strtolower($firstName).'@example.com',
            'status' => EntrepreneurStatus::CANDIDAT,
        ]);

        $project = Project::create([
            'sae_id' => $sae->id,
            'entrepreneur_id' => $entrepreneur->id,
            'name' => $projectName,
            'sector' => 'Agriculture',
            'status' => ProjectStatus::CANDIDAT,
        ]);

        $application = Application::create([
            'sae_id' => $sae->id,
            'program_id' => $program->id,
            'entrepreneur_id' => $entrepreneur->id,
            'project_id' => $project->id,
            'status' => ApplicationStatus::SOUMISE,
        ]);

        app(EvaluateApplicationByGridTool::class)->handle(
            new McpRequest([
                'application_id' => $application->id,
                'criteria_scores' => [
                    ['criterion_id' => $criterion->id, 'score' => $criterionScore, 'comment' => $comment],
                ],
                'summary_comment' => $comment,
            ]),
            app(ApplicationGridEvaluationService::class),
        );

        return $application;
    };

    $createEvaluatedApplication('Recale', 'Projet Faible', 6, 'Besoin de retravailler le modèle économique');
    $createEvaluatedApplication('Reussi', 'Projet Top', 18, 'Excellent impact et traction avérée');
    $createEvaluatedApplication('Attente', 'Projet Moyen', 12, 'Potentiel intéressant mais équipe à renforcer');

    $exportResponse = app(ExportApplicationEvaluationsSheetTool::class)->handle(
        new McpRequest(['program_id' => $program->id]),
        app(ApplicationGridEvaluationService::class),
    );

    $payload = json_decode((string) $exportResponse->responses()->first()->content(), true);

    expect($payload['summary'])->toBe([
        'total' => 3,
        'reussi' => 1,
        'liste_attente' => 1,
        'recale' => 1,
        'a_evaluer' => 0,
    ]);

    expect($payload['ranked_applications'][0]['project_name'])->toBe('Projet Top')
        ->and($payload['ranked_applications'][0]['recommendation_code'])->toBe('REUSSI')
        ->and($payload['ranked_applications'][1]['project_name'])->toBe('Projet Moyen')
        ->and($payload['ranked_applications'][1]['recommendation_code'])->toBe('LISTE_ATTENTE')
        ->and($payload['ranked_applications'][2]['project_name'])->toBe('Projet Faible')
        ->and($payload['ranked_applications'][2]['recommendation_code'])->toBe('RECALE');

    Storage::disk('public')->assertExists($payload['file_path']);

    $csv = Storage::disk('public')->get($payload['file_path']);
    expect($csv)->toContain('Projet Top')
        ->and($csv)->toContain('Réussi (Admissible)')
        ->and($csv)->toContain('Recalé')
        ->and($csv)->toContain('Besoin de retravailler le modèle économique');
});
