<?php

use App\Enums\ProjectStatus;
use App\Models\Entrepreneur;
use App\Models\Level;
use App\Models\Project;
use App\Models\ProjectLevelHistory;
use App\Models\Sae;
use App\Models\TimelineEvent;
use App\Models\User;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Teranga Africa',
        'slug' => 'teranga-africa',
    ]);

    $this->level1 = Level::firstOrCreate(
        ['sae_id' => $this->sae->id, 'name' => 'Idéation'],
        ['order' => 1]
    );

    $this->level2 = Level::firstOrCreate(
        ['sae_id' => $this->sae->id, 'name' => 'Prototypage'],
        ['order' => 2]
    );

    $this->level3 = Level::firstOrCreate(
        ['sae_id' => $this->sae->id, 'name' => 'Accélération'],
        ['order' => 3]
    );

    $entUser = User::factory()->create();
    $entUser->saes()->attach($this->sae->id);

    $this->entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $entUser->id,
        'first_name' => 'Moussa',
        'last_name' => 'Diop',
        'email' => $entUser->email,
        'status' => 'ACTIVE',
    ]);
});

test('project creation sets initial_level_id and records first level history', function () {
    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $this->entrepreneur->id,
        'name' => 'SolarTech Africa',
        'current_level_id' => $this->level1->id,
        'status' => ProjectStatus::ACTIF,
    ]);

    expect($project->initial_level_id)->toBe($this->level1->id)
        ->and($project->current_level_id)->toBe($this->level1->id)
        ->and($project->current_level_entered_at)->not->toBeNull()
        ->and($project->exit_level_id)->toBeNull()
        ->and($project->exited_at)->toBeNull();

    $history = ProjectLevelHistory::where('project_id', $project->id)->first();
    expect($history)->not->toBeNull()
        ->and($history->from_level_id)->toBeNull()
        ->and($history->to_level_id)->toBe($this->level1->id)
        ->and($history->ended_at)->toBeNull();
});

test('updating current_level_id closes previous history, creates new history, and logs timeline event', function () {
    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $this->entrepreneur->id,
        'name' => 'AgriBot Sahel',
        'current_level_id' => $this->level1->id,
        'status' => ProjectStatus::ACTIF,
    ]);

    // Promouvoir au niveau 2
    $project->update([
        'current_level_id' => $this->level2->id,
    ]);

    $project->refresh();

    expect($project->initial_level_id)->toBe($this->level1->id)
        ->and($project->current_level_id)->toBe($this->level2->id);

    // Ancien historique clôturé
    $oldHistory = ProjectLevelHistory::where('project_id', $project->id)
        ->where('to_level_id', $this->level1->id)
        ->first();

    expect($oldHistory->ended_at)->not->toBeNull();

    // Nouvel historique ouvert
    $newHistory = ProjectLevelHistory::where('project_id', $project->id)
        ->where('to_level_id', $this->level2->id)
        ->first();

    expect($newHistory)->not->toBeNull()
        ->and($newHistory->from_level_id)->toBe($this->level1->id)
        ->and($newHistory->ended_at)->toBeNull();

    // Timeline event enregistré
    $timelineEvent = TimelineEvent::where('project_id', $project->id)
        ->where('type', 'MILESTONE')
        ->first();

    expect($timelineEvent)->not->toBeNull()
        ->and($timelineEvent->title)->toContain($this->level2->name);
});

test('project graduation records exit_level_id and exited_at and closes open level history', function () {
    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $this->entrepreneur->id,
        'name' => 'FinPay Africa',
        'current_level_id' => $this->level2->id,
        'status' => ProjectStatus::ACTIF,
    ]);

    // Passer le projet en DIPLOME
    $project->update([
        'status' => ProjectStatus::DIPLOME,
    ]);

    $project->refresh();

    expect($project->exit_level_id)->toBe($this->level2->id)
        ->and($project->exited_at)->not->toBeNull()
        ->and($project->initial_level_id)->toBe($this->level2->id);

    $openHistories = ProjectLevelHistory::where('project_id', $project->id)
        ->whereNull('ended_at')
        ->count();

    expect($openHistories)->toBe(0);

    // Timeline exit event
    $exitEvent = TimelineEvent::where('project_id', $project->id)
        ->where('title', 'like', "%Fin d'accompagnement%")
        ->first();

    expect($exitEvent)->not->toBeNull()
        ->and($exitEvent->title)->toContain('Diplômé');
});
