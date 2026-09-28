<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('initial_level_id')->nullable()->after('status')->constrained('levels')->nullOnDelete();
            $table->foreignId('exit_level_id')->nullable()->after('current_level_entered_at')->constrained('levels')->nullOnDelete();
            $table->timestamp('exited_at')->nullable()->after('exit_level_id');
        });

        // Rétro-alimentation automatique des données existantes
        DB::statement('
            UPDATE projects 
            SET initial_level_id = COALESCE(
                (SELECT to_level_id FROM project_level_histories WHERE project_id = projects.id ORDER BY started_at ASC LIMIT 1),
                current_level_id
            )
            WHERE initial_level_id IS NULL AND current_level_id IS NOT NULL
        ');

        DB::statement("
            UPDATE projects 
            SET exit_level_id = current_level_id, exited_at = updated_at
            WHERE status IN ('SORTI', 'DIPLOME', 'ABANDONNE') AND exit_level_id IS NULL AND current_level_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['initial_level_id']);
            $table->dropForeign(['exit_level_id']);
            $table->dropColumn(['initial_level_id', 'exit_level_id', 'exited_at']);
        });
    }
};
