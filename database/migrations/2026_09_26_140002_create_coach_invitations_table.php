<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('token', 64)->unique();
            $table->json('specialties')->nullable();
            $table->unsignedInteger('max_capacity')->default(10);
            $table->boolean('is_external')->default(false);
            $table->string('status')->default('PENDING'); // PENDING | ACCEPTED | EXPIRED | CANCELLED
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sae_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_invitations');
    }
};
