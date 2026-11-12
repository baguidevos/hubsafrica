<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcp_oauth_clients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sae_id')->nullable()->constrained('saes')->cascadeOnDelete();
            $table->string('name');
            $table->string('client_id', 80)->unique();
            $table->string('client_secret', 120)->nullable();
            $table->json('redirect_uris')->nullable();
            $table->boolean('is_confidential')->default(true);
            $table->timestamps();
        });

        Schema::create('mcp_oauth_codes', function (Blueprint $table): void {
            $table->id();
            $table->string('code_hash', 64)->unique();
            $table->foreignId('oauth_client_id')->nullable()->constrained('mcp_oauth_clients')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->text('redirect_uri');
            $table->string('scope')->default('mcp:sae');
            $table->string('code_challenge', 128)->nullable();
            $table->string('code_challenge_method', 10)->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcp_oauth_codes');
        Schema::dropIfExists('mcp_oauth_clients');
    }
};
