<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->json('sectors')->nullable();
            $table->json('target_zones')->nullable();
            $table->longText('details');
            $table->string('learn_more_url')->nullable();
            $table->string('apply_url')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sae_id', 'is_active']);
            $table->index(['is_active', 'deadline']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
