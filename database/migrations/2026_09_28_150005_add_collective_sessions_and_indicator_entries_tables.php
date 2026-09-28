<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coaching_sessions', function (Blueprint $table) {
            $table->foreignId('cohort_id')->nullable()->after('entrepreneur_id')
                ->constrained('cohorts')->nullOnDelete();
            $table->foreignId('cohort_phase_id')->nullable()->after('cohort_id')
                ->constrained('cohort_phases')->nullOnDelete();
        });

        Schema::create('coaching_session_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coaching_session_id')->constrained('coaching_sessions')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->boolean('attended')->default(false);
            $table->text('feedback')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['coaching_session_id', 'project_id']);
        });

        Schema::create('indicator_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->cascadeOnDelete();
            $table->date('period_on');
            $table->decimal('value', 16, 2);
            $table->text('comment')->nullable();
            $table->foreignId('reported_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index(['sae_id', 'indicator_id', 'period_on']);
            $table->index(['project_id', 'period_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_entries');
        Schema::dropIfExists('coaching_session_attendees');

        Schema::table('coaching_sessions', function (Blueprint $table) {
            $table->dropForeign(['cohort_phase_id']);
            $table->dropColumn('cohort_phase_id');
            $table->dropForeign(['cohort_id']);
            $table->dropColumn('cohort_id');
        });
    }
};
