<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliverable_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('max_score')->default(100);
            $table->unsignedInteger('passing_score')->default(70);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->timestamp('deadline')->nullable();
            $table->timestamps();

            $table->index(['sae_id', 'program_id']);
        });

        Schema::create('deliverable_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('definition_id')->constrained('deliverable_definitions')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('entrepreneur_id')->nullable()->constrained('entrepreneurs')->nullOnDelete();
            $table->foreignId('submitted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('SOUMIS'); // SOUMIS | EN_EVALUATION | VALIDE | NON_VALIDE | CORRECTION_DEMANDEE
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->index(['sae_id', 'project_id', 'status']);
            $table->index(['sae_id', 'definition_id']);
        });

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('submission_id')->constrained('deliverable_submissions')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('score', 5, 2);
            $table->text('feedback')->nullable();
            $table->string('status'); // VALIDE | NON_VALIDE | CORRECTION_DEMANDEE
            $table->timestamp('evaluated_at')->useCurrent();
            $table->timestamps();

            $table->index(['sae_id', 'submission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('deliverable_submissions');
        Schema::dropIfExists('deliverable_definitions');
    }
};
