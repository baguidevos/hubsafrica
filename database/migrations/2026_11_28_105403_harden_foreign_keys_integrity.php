<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 1. Replace destructive ON DELETE CASCADE with ON DELETE SET NULL on optional FKs
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropForeign(['project_id']);
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->nullOnDelete();
        });

        Schema::table('deliverable_submissions', function (Blueprint $table): void {
            $table->dropForeign(['project_id']);
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->nullOnDelete();
        });

        Schema::table('indicator_entries', function (Blueprint $table): void {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['enrollment_id']);

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->nullOnDelete();

            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->nullOnDelete();
        });

        // 2. Defensively clean up any orphaned references before adding missing FKs
        DB::table('form_builder_forms')
            ->whereNotNull('sae_id')
            ->whereNotIn('sae_id', DB::table('saes')->select('id'))
            ->update(['sae_id' => null]);

        DB::table('form_builder_submissions')
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->update(['user_id' => null]);

        DB::table('form_unlocks')
            ->whereNotIn('form_id', DB::table('form_builder_forms')->select('id'))
            ->delete();

        DB::table('form_unlocks')
            ->whereNotNull('submission_id')
            ->whereNotIn('submission_id', DB::table('form_builder_submissions')->select('id'))
            ->update(['submission_id' => null]);

        DB::table('form_attempts')
            ->whereNotIn('form_id', DB::table('form_builder_forms')->select('id'))
            ->delete();

        // 3. Add missing foreign key constraints on form tables
        Schema::table('form_builder_forms', function (Blueprint $table): void {
            $table->foreign('sae_id')
                ->references('id')
                ->on('saes')
                ->nullOnDelete();
        });

        Schema::table('form_builder_submissions', function (Blueprint $table): void {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('form_unlocks', function (Blueprint $table): void {
            $table->foreign('form_id')
                ->references('id')
                ->on('form_builder_forms')
                ->cascadeOnDelete();

            $table->foreign('submission_id')
                ->references('id')
                ->on('form_builder_submissions')
                ->nullOnDelete();
        });

        Schema::table('form_attempts', function (Blueprint $table): void {
            $table->foreign('form_id')
                ->references('id')
                ->on('form_builder_forms')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('form_attempts', function (Blueprint $table): void {
            $table->dropForeign(['form_id']);
        });

        Schema::table('form_unlocks', function (Blueprint $table): void {
            $table->dropForeign(['form_id']);
            $table->dropForeign(['submission_id']);
        });

        Schema::table('form_builder_submissions', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        Schema::table('form_builder_forms', function (Blueprint $table): void {
            $table->dropForeign(['sae_id']);
        });

        Schema::table('indicator_entries', function (Blueprint $table): void {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['enrollment_id']);

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->cascadeOnDelete();

            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->cascadeOnDelete();
        });

        Schema::table('deliverable_submissions', function (Blueprint $table): void {
            $table->dropForeign(['project_id']);
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->cascadeOnDelete();
        });

        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropForeign(['project_id']);
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->cascadeOnDelete();
        });
    }
};
