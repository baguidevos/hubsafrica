<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->default(1);
            $table->string('color', 32)->nullable();
            $table->text('description')->nullable();
            $table->decimal('min_average', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['sae_id', 'name']);
            $table->index(['sae_id', 'order']);
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->string('target')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('registration_start')->nullable();
            $table->date('registration_end')->nullable();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->text('eligibility')->nullable();
            $table->string('status')->default('BROUILLON'); // BROUILLON | OUVERT_CANDIDATURES | CANDIDATURES_FERMEES | SELECTION | LANCE | SUIVI | CLOTURE | ARCHIVE
            $table->timestamps();

            $table->index(['sae_id', 'status']);
        });

        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('number')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('objectives')->nullable();
            $table->string('status')->default('OUVERTE'); // OUVERTE | EN_COURS | CLOTUREE
            $table->timestamps();

            $table->index(['sae_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohorts');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('levels');
    }
};
