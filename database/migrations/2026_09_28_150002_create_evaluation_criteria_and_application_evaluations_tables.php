<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('weight')->default(1);
            $table->unsignedSmallInteger('max_score')->default(10);
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();

            $table->index(['sae_id', 'program_id']);
        });

        Schema::create('application_evaluators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained('cohorts')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('JURY');
            $table->timestamps();

            $table->index(['sae_id', 'user_id']);
            $table->index(['sae_id', 'cohort_id']);
        });

        Schema::create('application_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment')->nullable();
            $table->decimal('total_score', 6, 2)->nullable();
            $table->string('status')->default('BROUILLON');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'evaluator_id']);
            $table->index(['sae_id', 'application_id', 'total_score']);
        });

        Schema::create('application_evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('application_evaluations')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('evaluation_criteria')->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['evaluation_id', 'criterion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_evaluation_scores');
        Schema::dropIfExists('application_evaluations');
        Schema::dropIfExists('application_evaluators');
        Schema::dropIfExists('evaluation_criteria');
    }
};
