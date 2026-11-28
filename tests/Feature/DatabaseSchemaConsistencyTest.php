<?php

use App\Enums\DeliverableSubmissionStatus;
use App\Models\Entrepreneur;
use App\Models\Sae;
use Illuminate\Support\Facades\Schema;

test('entrepreneurs table supports fillable city attribute', function () {
    $sae = Sae::create([
        'name' => 'SAE Schema Audit',
        'slug' => 'sae-schema-audit-'.uniqid(),
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Kossi',
        'last_name' => 'Mensah',
        'email' => 'kossi-'.uniqid().'@example.com',
        'country' => 'Togo',
        'territory' => 'Maritime',
        'city' => 'Lomé',
        'status' => 'ACTIVE',
    ]);

    expect($entrepreneur->fresh()->city)->toBe('Lomé');
});

test('unused legacy events tables are dropped and expected audit and tenant indexes exist', function () {
    expect(Schema::hasTable('events'))->toBeFalse()
        ->and(Schema::hasTable('event_user'))->toBeFalse()
        ->and(Schema::hasIndex('audit_logs', 'audit_logs_entity_entity_id_index'))->toBeTrue()
        ->and(Schema::hasIndex('deliverable_submissions', 'deliverable_submissions_sae_id_status_index'))->toBeTrue()
        ->and(Schema::hasIndex('documents', 'documents_documentable_type_documentable_id_index'))->toBeFalse();
});

test('deliverable submission status enum only exposes canonical statuses without duplicates', function () {
    $values = array_map(
        fn (DeliverableSubmissionStatus $status): string => $status->value,
        DeliverableSubmissionStatus::cases()
    );

    expect($values)->toBe([
        'BROUILLON',
        'SOUMIS',
        'EN_EVALUATION',
        'VALIDE',
        'REJETEE',
        'CORRECTION_DEMANDEE',
    ]);
});
