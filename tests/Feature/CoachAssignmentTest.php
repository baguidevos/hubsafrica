<?php

use App\Enums\CoachAssignmentStatus;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\TimelineEvent;
use App\Models\User;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Teranga Africa',
        'slug' => 'teranga-africa',
    ]);
});

test('a coach can be assigned to a project with cohort, program and timeline event', function () {
    $coachUser = User::factory()->create();
    $coachUser->saes()->attach($this->sae->id);

    $coach = Coach::create([
        'sae_id' => $this->sae->id,
        'user_id' => $coachUser->id,
        'name' => 'Amadou Kane',
        'email' => $coachUser->email,
        'max_capacity' => 6,
        'status' => 'ACTIF',
    ]);

    $entUser = User::factory()->create();
    $entUser->saes()->attach($this->sae->id);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $entUser->id,
        'first_name' => 'Aissatou',
        'last_name' => 'Sow',
        'email' => $entUser->email,
        'status' => 'ACTIVE',
    ]);

    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme AgriTech',
        'slug' => 'programme-agritech',
        'status' => 'ACTIF',
    ]);

    $cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte 1 AgriTech',
        'number' => 1,
        'capacity' => 10,
        'status' => 'EN_COURS',
    ]);

    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'BioFerme Thiès',
        'slug' => 'bioferme-thies',
        'status' => 'ACTIF',
    ]);

    Enrollment::create([
        'sae_id' => $this->sae->id,
        'cohort_id' => $cohort->id,
        'project_id' => $project->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => 'ACTIF',
        'enrolled_at' => now(),
    ]);

    // Create assignment
    $assignment = CoachAssignment::create([
        'sae_id' => $this->sae->id,
        'coach_id' => $coach->id,
        'project_id' => $project->id,
        'program_id' => $program->id,
        'cohort_id' => $cohort->id,
        'assigned_at' => now(),
        'status' => CoachAssignmentStatus::ACTIF,
    ]);

    expect($assignment->coach->id)->toBe($coach->id)
        ->and($assignment->project->id)->toBe($project->id)
        ->and($assignment->cohort->id)->toBe($cohort->id)
        ->and($assignment->program->id)->toBe($program->id)
        ->and($coach->fresh()->activeAssignmentsCount())->toBe(1);

    // End assignment
    $assignment->update([
        'status' => CoachAssignmentStatus::TERMINE,
        'ended_at' => now(),
    ]);

    expect($coach->fresh()->activeAssignmentsCount())->toBe(0);
});
