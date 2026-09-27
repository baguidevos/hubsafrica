<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaching_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('coach_id')->constrained('coaches')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('entrepreneur_id')->nullable()->constrained('entrepreneurs')->nullOnDelete();
            $table->dateTime('date');
            $table->unsignedInteger('duration')->default(60); // minutes
            $table->string('type'); // INDIVIDUEL | COLLECTIF | MENTORAT | ATELIER | FORMATION | SUIVI
            $table->string('theme')->nullable();
            $table->text('report')->nullable();
            $table->text('objectives')->nullable();
            $table->text('next_actions')->nullable();
            $table->string('status')->default('PLANIFIEE'); // PLANIFIEE | REALISEE | ANNULEE
            $table->timestamps();

            $table->index(['sae_id', 'coach_id']);
            $table->index(['sae_id', 'project_id']);
            $table->index(['sae_id', 'date']);
        });

        Schema::create('funding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->decimal('amount_sought', 15, 2)->nullable();
            $table->decimal('amount_obtained', 15, 2)->nullable();
            $table->decimal('amount_invested', 15, 2)->nullable();
            $table->string('source')->nullable();
            $table->string('type')->nullable(); // GRANT | LOAN | EQUITY | SUBSIDY | PRIZE | OTHER
            $table->string('status')->default('DEMANDE'); // DEMANDE | ACCORDE | DECAISSE | INVESTI
            $table->date('date')->nullable();
            $table->text('justification')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();

            $table->index(['sae_id', 'project_id']);
            $table->index(['sae_id', 'status']);
        });

        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->string('name');
            $table->text('definition')->nullable();
            $table->text('formula')->nullable();
            $table->string('source')->nullable();
            $table->string('frequency')->nullable(); // MENSUEL | TRIMESTRIEL | ANNUEL
            $table->string('responsible')->nullable();
            $table->string('category')->default('PORTEE'); // PORTEE | INCLUSION | PORTEFEUILLE | MISE_EN_OEUVRE | PROGRESSION | LIVRABLES | FINANCEMENT | QUALITE
            $table->string('unit')->nullable(); // % | nombre | FCFA
            $table->decimal('target', 15, 2)->nullable();
            $table->decimal('current', 15, 2)->nullable();
            $table->decimal('previous', 15, 2)->nullable();
            $table->string('status')->nullable(); // ATTEINT | EN_BONNE_VOIE | EN_RETARD | NON_MESURABLE
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['sae_id', 'category']);
        });

        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // VIDEO | DOCUMENT | LIEN_EXTERNE
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->string('category')->nullable();
            $table->string('author')->nullable();
            $table->string('visibility')->default('TOUS'); // TOUS | RESPONSABLE | COACH | ENTREPRENEUR
            $table->timestamps();

            $table->index(['sae_id', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
        Schema::dropIfExists('indicators');
        Schema::dropIfExists('funding_records');
        Schema::dropIfExists('coaching_sessions');
    }
};
