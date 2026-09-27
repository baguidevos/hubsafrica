<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('entrepreneur_id')->constrained('entrepreneurs')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('problem')->nullable();
            $table->text('solution')->nullable();
            $table->text('product_service')->nullable();
            $table->text('value_proposition')->nullable();
            $table->string('target')->nullable();
            $table->string('market')->nullable();
            $table->string('sector')->nullable();
            $table->string('territory')->nullable();
            $table->json('sdgs')->nullable();
            $table->string('logo')->nullable();
            $table->text('pitch')->nullable();
            $table->unsignedInteger('team_size')->default(1);
            $table->string('status')->default('ACTIF'); // ACTIF | BLOQUE | STAGNANT | SORTI | ABANDONNE | DIPLOME
            $table->foreignId('current_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->timestamp('current_level_entered_at')->nullable();
            $table->timestamps();

            $table->index(['sae_id', 'status']);
            $table->index(['sae_id', 'sector']);
            $table->index(['sae_id', 'entrepreneur_id']);
        });

        Schema::create('project_level_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('from_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('to_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sae_id', 'project_id']);
        });

        Schema::create('project_update_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('changed_by_role')->default('ENTREPRENEUR');
            $table->string('changed_by_name');
            $table->json('changes'); // array [{field, label, oldValue, newValue}]
            $table->timestamps();

            $table->index(['sae_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_update_logs');
        Schema::dropIfExists('project_level_histories');
        Schema::dropIfExists('projects');
    }
};
