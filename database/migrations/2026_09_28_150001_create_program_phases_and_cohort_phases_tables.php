<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('passing_criteria')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->unsignedSmallInteger('duration_weeks')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'position']);
            $table->index(['sae_id', 'program_id']);
        });

        Schema::create('cohort_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained('cohorts')->cascadeOnDelete();
            $table->foreignId('program_phase_id')->nullable()->constrained('program_phases')->nullOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('position')->default(1);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('status')->default('A_VENIR');
            $table->timestamps();

            $table->unique(['cohort_id', 'position']);
            $table->index(['sae_id', 'cohort_id', 'status']);
        });

        Schema::table('deliverable_definitions', function (Blueprint $table) {
            $table->foreignId('program_phase_id')->nullable()->after('level_id')
                ->constrained('program_phases')->nullOnDelete();
        });

        Schema::table('deliverable_submissions', function (Blueprint $table) {
            $table->foreignId('cohort_phase_id')->nullable()->after('definition_id')
                ->constrained('cohort_phases')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deliverable_submissions', function (Blueprint $table) {
            $table->dropForeign(['cohort_phase_id']);
            $table->dropColumn('cohort_phase_id');
        });

        Schema::table('deliverable_definitions', function (Blueprint $table) {
            $table->dropForeign(['program_phase_id']);
            $table->dropColumn('program_phase_id');
        });

        Schema::dropIfExists('cohort_phases');
        Schema::dropIfExists('program_phases');
    }
};
