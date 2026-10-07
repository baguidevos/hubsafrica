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
        Schema::table('deliverable_definitions', function (Blueprint $table) {
            $table->string('target_type')->default('STARTUP')->after('program_phase_id'); // STARTUP | PROGRAMME
            $table->string('responsible_role')->nullable()->after('target_type'); // FONDATEUR | COACH | COORDINATEUR | FORMATEUR | CHARGE_ME | SAEI
            $table->unsignedSmallInteger('sequence_order')->nullable()->after('responsible_role');
        });

        Schema::table('deliverable_submissions', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->change();
            $table->foreignId('cohort_id')->nullable()->after('project_id')->constrained('cohorts')->nullOnDelete();
            $table->boolean('is_late')->default(false)->after('submitted_at');
        });

        Schema::table('coaching_sessions', function (Blueprint $table) {
            $table->unsignedSmallInteger('session_number')->nullable()->after('duration');
            $table->string('period_of_day')->nullable()->after('session_number'); // MATIN | APRES_MIDI | JOURNEE_COMPLETE
            $table->decimal('hours_delivered', 4, 2)->nullable()->after('period_of_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coaching_sessions', function (Blueprint $table) {
            $table->dropColumn(['hours_delivered', 'period_of_day', 'session_number']);
        });

        Schema::table('deliverable_submissions', function (Blueprint $table) {
            $table->dropColumn('is_late');
            $table->dropForeign(['cohort_id']);
            $table->dropColumn('cohort_id');
        });

        Schema::table('deliverable_definitions', function (Blueprint $table) {
            $table->dropColumn(['sequence_order', 'responsible_role', 'target_type']);
        });
    }
};
