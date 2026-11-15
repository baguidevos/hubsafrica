<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accompaniment_tools', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('format', 32);
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->text('drive_url')->nullable();
            $table->string('access_level', 32);
            $table->unsignedInteger('price')->nullable();
            $table->string('currency', 10)->default('XOF');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('accompaniment_tool_access_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accompaniment_tool_id')->constrained('accompaniment_tools')->cascadeOnDelete();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32);
            $table->text('request_message')->nullable();
            $table->text('decision_note')->nullable();
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['accompaniment_tool_id', 'user_id'], 'tool_access_req_unique');
        });

        Schema::create('accompaniment_tool_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accompaniment_tool_id')->constrained('accompaniment_tools')->cascadeOnDelete();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference', 80)->unique();
            $table->string('external_reference')->nullable();
            $table->string('gateway', 40);
            $table->unsignedInteger('amount');
            $table->string('currency', 10)->default('XOF');
            $table->string('status', 32);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accompaniment_tool_payments');
        Schema::dropIfExists('accompaniment_tool_access_requests');
        Schema::dropIfExists('accompaniment_tools');
    }
};
