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
        Schema::create('sae_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->nullable()->default('primary');
            $table->string('icon')->nullable()->default('heroicon-o-building-office-2');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sae_sae_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('sae_type_id')->constrained('sae_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sae_id', 'sae_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sae_sae_type');
        Schema::dropIfExists('sae_types');
    }
};
