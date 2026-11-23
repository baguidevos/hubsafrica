<?php

use App\Enums\ApplicationStatus;
use App\Enums\DocumentCategory;
use App\Enums\EntrepreneurStatus;
use App\Enums\FormType;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Filament\Admin\Widgets\PlatformDataQualityOverviewWidget;
use App\Filament\Coach\Resources\Projects\Pages\EditProject as CoachEditProject;
use App\Filament\Coach\Resources\Projects\ProjectResource as CoachProjectResource;
use App\Filament\Entrepreneur\Resources\Projects\Pages\EditProject as EntrepreneurEditProject;
use App\Filament\Entrepreneur\Resources\Projects\ProjectResource as EntrepreneurProjectResource;
use App\Filament\Sae\Widgets\SaeDataQualityOverviewWidget;
use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\Document;
use App\Models\Entrepreneur;
use App\Models\Form;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\TimelineEvent;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('entrepreneur and coach cannot delete projects from resources or edit pages', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Guard',
        'slug' => 'sae-guard-'.uniqid(),
    ]);

    $entUser = User::factory()->create();
    $entUser->saes()->attach($sae->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);
    $entrepreneur = Entrepreneur::create([
        'user_id' => $entUser->id,
        'sae_id' => $sae->id,
        'first_name' => 'Kofi',
        'last_name' => 'Mensah',
        'email' => $entUser->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Projet Protégé',
        'status' => ProjectStatus::ACTIF,
    ]);

    $coachUser = User::factory()->create();
    $coachUser->saes()->attach($sae->id, ['role' => 'COACH', 'status' => 'ACTIVE']);
    $coach = Coach::create([
        'user_id' => $coachUser->id,
        'sae_id' => $sae->id,
        'name' => 'Coach Test',
        'email' => $coachUser->email,
        'status' => 'ACTIF',
    ]);
    CoachAssignment::create([
        'sae_id' => $sae->id,
        'coach_id' => $coach->id,
        'project_id' => $project->id,
        'status' => 'ACTIF',
    ]);

    // Côté Entrepreneur
    $this->actingAs($entUser);
    Filament::setTenant($sae);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));

    expect(EntrepreneurProjectResource::canDelete($project))->toBeFalse()
        ->and(EntrepreneurProjectResource::canDeleteAny())->toBeFalse();

    Livewire::test(EntrepreneurEditProject::class, ['record' => $project->id])
        ->assertSuccessful()
        ->assertActionDoesNotExist(DeleteAction::class);

    // Côté Coach
    $this->actingAs($coachUser);
    Filament::setTenant($sae);
    Filament::setCurrentPanel(Filament::getPanel('coach'));

    expect(CoachProjectResource::canDelete($project))->toBeFalse()
        ->and(CoachProjectResource::canDeleteAny())->toBeFalse();

    Livewire::test(CoachEditProject::class, ['record' => $project->id])
        ->assertSuccessful()
        ->assertActionDoesNotExist(DeleteAction::class);
});

test('sae:fix-candidate-stats repairs orphan active entrepreneurs, orphan applications, and untyped candidature forms', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Repair',
        'slug' => 'sae-repair-'.uniqid(),
    ]);
    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'AgriPulse',
        'status' => ProgramStatus::ACTIF,
    ]);

    // 1. Entrepreneur ACTIVE sans projet ni candidature (abandon inscription cohorte)
    $orphanEnt = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Abandon',
        'last_name' => 'Inscription',
        'email' => 'abandon@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    // 2. Candidature orpheline (project_id = null) avec document Pitch Deck + TimelineEvent sans project_id
    $entWithOrphanApp = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Pierre',
        'last_name' => 'Drame',
        'email' => 'pierre@example.com',
        'status' => EntrepreneurStatus::CANDIDAT,
    ]);
    $orphanApp = Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'entrepreneur_id' => $entWithOrphanApp->id,
        'project_id' => null,
        'status' => ApplicationStatus::SOUMISE,
    ]);
    Document::create([
        'sae_id' => $sae->id,
        'documentable_type' => Application::class,
        'documentable_id' => $orphanApp->id,
        'category' => DocumentCategory::CANDIDATURE,
        'title' => 'Pitch Deck — Guinea Green-Maize & Feed Solutions',
        'disk' => 'local',
        'path' => 'form-builder/12/deck.pdf',
        'original_name' => 'deck.pdf',
        'version' => 1,
    ]);
    $timeline = TimelineEvent::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entWithOrphanApp->id,
        'project_id' => null,
        'type' => 'MILESTONE',
        'title' => 'Candidature déposée',
    ]);

    // 3. Formulaire de candidature avec type = NULL en base (ex: BIORYVA)
    $untypedForm = Form::create([
        'sae_id' => $sae->id,
        'name' => 'Formulaire de candidature – BIORYVA',
        'slug' => 'bioryva-'.uniqid(),
    ]);
    DB::table('form_builder_forms')->where('id', $untypedForm->id)->update(['type' => null]);

    // Dry-run : aucune écriture
    $this->artisan('sae:fix-candidate-stats', ['--dry-run' => true])->assertSuccessful();
    expect($orphanEnt->fresh()->status)->toBe(EntrepreneurStatus::ACTIVE)
        ->and($orphanApp->fresh()->project_id)->toBeNull()
        ->and(DB::table('form_builder_forms')->where('id', $untypedForm->id)->value('type'))->toBeNull();

    // Exécution réelle
    $this->artisan('sae:fix-candidate-stats')->assertSuccessful();

    $repairedApp = $orphanApp->fresh();
    expect($orphanEnt->fresh()->status)->toBe(EntrepreneurStatus::CANDIDAT)
        ->and($repairedApp->project_id)->not->toBeNull()
        ->and($repairedApp->project->name)->toBe('Guinea Green-Maize & Feed Solutions')
        ->and($repairedApp->project->status)->toBe(ProjectStatus::CANDIDAT)
        ->and($timeline->fresh()->project_id)->toBe($repairedApp->project_id)
        ->and($untypedForm->fresh()->type)->toBe(FormType::CANDIDATURE);
});

test('sae and admin data quality overview widgets display explicit onboarding and anomaly metrics', function () {
    $admin = User::factory()->create(['email' => 'admin@hubsafrica.org']);
    $sae = Sae::create([
        'owner_id' => $admin->id,
        'name' => 'SAE Qualité',
        'slug' => 'sae-qualite-'.uniqid(),
    ]);
    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Prog Qualité',
        'status' => ProgramStatus::ACTIF,
    ]);

    // 1 inscrit orphelin en ACTIVE
    Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Orphelin',
        'last_name' => 'Actif',
        'email' => 'orphelin-actif@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    // 1 candidature sans projet
    $candEnt = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Candidat',
        'last_name' => 'SansProjet',
        'email' => 'cand-sans-projet@example.com',
        'status' => EntrepreneurStatus::CANDIDAT,
    ]);
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'entrepreneur_id' => $candEnt->id,
        'project_id' => null,
        'status' => ApplicationStatus::SOUMISE,
    ]);

    // Widget SAE
    $this->actingAs($admin);
    Filament::setTenant($sae);
    Filament::setCurrentPanel(Filament::getPanel('sae'));

    Livewire::test(SaeDataQualityOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Inscrits sans projet ni candidature')
        ->assertSee('1 en ACTIVE')
        ->assertSee('Candidatures sans projet lié')
        ->assertSee('Anomalie : projet supprimé ou manquant');

    // Widget Admin Hubs-HQ
    Filament::setTenant(null);
    Filament::setCurrentPanel(Filament::getPanel('hubs-hq'));

    Livewire::test(PlatformDataQualityOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Comptes sans projet ni candidature')
        ->assertSee('Candidatures sans projet lié')
        ->assertSee('Formulaires candidature non typés');
});
