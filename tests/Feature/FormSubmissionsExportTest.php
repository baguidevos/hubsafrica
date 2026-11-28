<?php

use App\Enums\CohortStatus;
use App\Enums\FormType;
use App\Enums\ProgramStatus;
use App\Models\Cohort;
use App\Models\Form;
use App\Models\Program;
use App\Models\Sae;
use App\Services\FormSubmissionsExportService;
use Packstub\FormBuilder\Models\FormSubmission;

test('form submissions export replaces program_id and cohort_id with readable names in csv and xlsx', function () {
    $sae = Sae::create([
        'name' => 'SAE Submissions Export',
        'slug' => 'sae-submissions-export',
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme AgriPulse',
        'status' => ProgramStatus::ACTIF,
        'capacity' => 50,
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'AgriPulse Cohorte 1 - Pilote',
        'status' => CohortStatus::OUVERTE,
        'capacity' => 50,
    ]);

    $form = Form::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Formulaire Candidature Test',
        'slug' => 'formulaire-candidature-test-'.uniqid(),
        'type' => FormType::CANDIDATURE,
        'is_active' => true,
        'fields' => [
            [
                'type' => 'text',
                'data' => [
                    'key' => 'project_name',
                    'label' => 'Nom du projet',
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
    ]);

    FormSubmission::create([
        'form_id' => $form->id,
        'number' => 1,
        'data' => [
            'project_name' => 'SolarFarm',
            'program_id' => (string) $program->id,
            'cohort_id' => (string) $cohort->id,
        ],
    ]);

    [$header, $rowsGenerator] = FormSubmissionsExportService::rows($form, $form->submissions()->getQuery());
    $rows = iterator_to_array($rowsGenerator);

    expect($header)
        ->toContain('Programme')
        ->toContain('Cohorte')
        ->not->toContain('Programme ID')
        ->not->toContain('Cohorte ID');

    $programIndex = array_search('Programme', $header, true);
    $cohortIndex = array_search('Cohorte', $header, true);

    expect($rows[0][$programIndex])->toBe('Programme AgriPulse')
        ->and($rows[0][$cohortIndex])->toBe('AgriPulse Cohorte 1 - Pilote');

    // CSV streaming check
    $csvResponse = FormSubmissionsExportService::download($form, $form->submissions()->getQuery(), 'csv');
    ob_start();
    $csvResponse->sendContent();
    $csvContent = (string) ob_get_clean();

    expect($csvContent)
        ->toContain('Programme')
        ->toContain('Cohorte')
        ->toContain('Programme AgriPulse')
        ->toContain('AgriPulse Cohorte 1 - Pilote');
});
