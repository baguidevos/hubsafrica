<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained('cohorts')->nullOnDelete();
            $table->foreignId('entrepreneur_id')->constrained('entrepreneurs')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('status')->default('SOUMISE'); // BROUILLON | SOUMISE | EN_EXAMEN | ACCEPTEE | REFUSEE | REORIENTEE | EN_ATTENTE | RETIREE
            $table->unsignedInteger('score')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision')->nullable();
            $table->text('comment')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['sae_id', 'status']);
            $table->index(['sae_id', 'program_id']);
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained('cohorts')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('entrepreneur_id')->constrained('entrepreneurs')->cascadeOnDelete();
            $table->string('status')->default('ACTIF'); // ACTIF | TERMINE | ABANDON
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();

            $table->index(['sae_id', 'cohort_id']);
            $table->index(['sae_id', 'status']);
        });

        Schema::create('coach_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('coach_id')->constrained('coaches')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained('cohorts')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->string('status')->default('ACTIF'); // ACTIF | TERMINE
            $table->timestamps();

            $table->index(['sae_id', 'coach_id', 'status']);
            $table->index(['sae_id', 'project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_assignments');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('applications');
    }
};
