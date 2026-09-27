<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('specialties')->nullable();
            $table->string('availability')->nullable();
            $table->unsignedInteger('max_capacity')->default(10);
            $table->boolean('is_external')->default(false);
            $table->string('status')->default('ACTIVE'); // ACTIVE | INACTIVE
            $table->timestamps();

            $table->index(['sae_id', 'status']);
        });

        Schema::create('entrepreneurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender', 16)->nullable(); // F | M | AUTRE
            $table->date('birth_date')->nullable();
            $table->string('country')->nullable();
            $table->string('territory')->nullable();
            $table->string('education_level')->nullable();
            $table->string('profession')->nullable();
            $table->string('professional_status')->nullable();
            $table->string('legal_status')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE | ABANDONNE | DIPLOME | SUSPENDU
            $table->timestamps();

            $table->index(['sae_id', 'status']);
            $table->index(['sae_id', 'gender']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrepreneurs');
        Schema::dropIfExists('coaches');
    }
};
