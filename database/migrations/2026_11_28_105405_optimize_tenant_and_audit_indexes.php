<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $saeStatusTables = [
        'accompaniment_tool_access_requests',
        'accompaniment_tool_payments',
        'application_evaluations',
        'coaching_sessions',
        'coach_assignments',
        'cohort_phases',
        'deliverable_submissions',
        'evaluations',
        'form_unlocks',
        'indicators',
        'sae_users',
    ];

    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(['entity', 'entity_id'], 'audit_logs_entity_entity_id_index');
        });

        foreach ($this->saeStatusTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->index(['sae_id', 'status'], "{$tableName}_sae_id_status_index");
            });
        }

        if (Schema::hasIndex('documents', 'documents_documentable_type_documentable_id_index')) {
            Schema::table('documents', function (Blueprint $table): void {
                $table->dropIndex('documents_documentable_type_documentable_id_index');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('documents', 'documents_documentable_type_documentable_id_index')) {
            Schema::table('documents', function (Blueprint $table): void {
                $table->index(['documentable_type', 'documentable_id'], 'documents_documentable_type_documentable_id_index');
            });
        }

        foreach ($this->saeStatusTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropIndex("{$tableName}_sae_id_status_index");
            });
        }

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_entity_entity_id_index');
        });
    }
};
