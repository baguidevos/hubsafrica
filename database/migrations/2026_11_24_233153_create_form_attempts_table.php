<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->nullable()->constrained('saes')->nullOnDelete();
            $table->unsignedBigInteger('form_id');
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained('cohorts')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('entrepreneur_id')->nullable()->constrained('entrepreneurs')->nullOnDelete();
            $table->string('status', 32)->default('EN_COURS');
            $table->string('last_step_label')->nullable();
            $table->unsignedSmallInteger('filled_fields_count')->default(0);
            $table->unsignedSmallInteger('total_fields_count')->default(0);
            $table->string('last_filled_field')->nullable();
            $table->text('last_error_reason')->nullable();
            $table->json('validation_errors')->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['sae_id', 'status']);
            $table->index(['form_id', 'user_id']);
            $table->index(['sae_id', 'cohort_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_attempts');
    }
};
