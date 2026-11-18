<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accompaniment_tools', function (Blueprint $table): void {
            $table->boolean('is_shared_with_network')
                ->default(false)
                ->after('is_published');

            $table->index(['is_published', 'is_shared_with_network'], 'tools_published_network_idx');
        });

        Schema::create('accompaniment_tool_sae_type', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accompaniment_tool_id')
                ->constrained('accompaniment_tools')
                ->cascadeOnDelete();
            $table->foreignId('sae_type_id')
                ->constrained('sae_types')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['accompaniment_tool_id', 'sae_type_id'], 'tool_sae_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accompaniment_tool_sae_type');

        Schema::table('accompaniment_tools', function (Blueprint $table): void {
            $table->dropIndex('tools_published_network_idx');
            $table->dropColumn('is_shared_with_network');
        });
    }
};
