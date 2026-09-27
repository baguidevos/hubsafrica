<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saes', function (Blueprint $table) {
            $table->id();
            // Contrainte d'unicité : un utilisateur ne peut créer qu'une seule SAE
            $table->foreignId('owner_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE | INACTIVE
            $table->timestamps();
        });

        Schema::create('sae_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('RESPONSABLE'); // RESPONSABLE | COACH | ENTREPRENEUR
            $table->string('status')->default('ACTIVE'); // ACTIVE | INACTIVE | INVITED
            $table->timestamps();

            $table->unique(['sae_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sae_users');
        Schema::dropIfExists('saes');
    }
};
