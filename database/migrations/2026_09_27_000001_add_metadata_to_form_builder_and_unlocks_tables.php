<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $formsTable = config('packstub-form-builder.tables.forms', 'form_builder_forms');

        Schema::table($formsTable, function (Blueprint $table): void {
            if (! Schema::hasColumn($table->getTable(), 'program_id')) {
                $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            }
            if (! Schema::hasColumn($table->getTable(), 'type')) {
                $table->string('type')->nullable()->index();
            }
            if (! Schema::hasColumn($table->getTable(), 'target_level_id')) {
                $table->foreignId('target_level_id')->nullable()->constrained('levels')->nullOnDelete();
            }
            if (! Schema::hasColumn($table->getTable(), 'is_mandatory')) {
                $table->boolean('is_mandatory')->default(false);
            }
        });

        Schema::table('form_unlocks', function (Blueprint $table): void {
            if (! Schema::hasColumn('form_unlocks', 'status')) {
                $table->string('status')->default('EN_ATTENTE')->index();
            }
            if (! Schema::hasColumn('form_unlocks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
            if (! Schema::hasColumn('form_unlocks', 'submission_id')) {
                $table->unsignedBigInteger('submission_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        $formsTable = config('packstub-form-builder.tables.forms', 'form_builder_forms');

        Schema::table($formsTable, function (Blueprint $table): void {
            $table->dropForeign(['program_id']);
            $table->dropForeign(['target_level_id']);
            $table->dropColumn(['program_id', 'type', 'target_level_id', 'is_mandatory']);
        });

        Schema::table('form_unlocks', function (Blueprint $table): void {
            $table->dropColumn(['status', 'completed_at', 'submission_id']);
        });
    }
};
