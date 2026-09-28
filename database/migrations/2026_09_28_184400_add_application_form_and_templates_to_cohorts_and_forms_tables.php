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
        $formsTable = config('packstub-form-builder.tables.forms', 'form_builder_forms');

        Schema::table($formsTable, function (Blueprint $table): void {
            if (! Schema::hasColumn($table->getTable(), 'is_template')) {
                $table->boolean('is_template')->default(false)->index();
            }
            if (! Schema::hasColumn($table->getTable(), 'is_standard')) {
                $table->boolean('is_standard')->default(false)->index();
            }
        });

        Schema::table('cohorts', function (Blueprint $table) use ($formsTable): void {
            if (! Schema::hasColumn('cohorts', 'application_form_id')) {
                $table->foreignId('application_form_id')->nullable()->constrained($formsTable)->nullOnDelete();
            }
        });

        Schema::table('applications', function (Blueprint $table) use ($formsTable): void {
            if (! Schema::hasColumn('applications', 'form_id')) {
                $table->foreignId('form_id')->nullable()->constrained($formsTable)->nullOnDelete();
            }
            if (! Schema::hasColumn('applications', 'answers')) {
                $table->json('answers')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $formsTable = config('packstub-form-builder.tables.forms', 'form_builder_forms');

        Schema::table('applications', function (Blueprint $table): void {
            if (Schema::hasColumn('applications', 'form_id')) {
                $table->dropForeign(['form_id']);
                $table->dropColumn(['form_id']);
            }
            if (Schema::hasColumn('applications', 'answers')) {
                $table->dropColumn(['answers']);
            }
        });

        Schema::table('cohorts', function (Blueprint $table): void {
            if (Schema::hasColumn('cohorts', 'application_form_id')) {
                $table->dropForeign(['application_form_id']);
                $table->dropColumn(['application_form_id']);
            }
        });

        Schema::table($formsTable, function (Blueprint $table): void {
            if (Schema::hasColumn($table->getTable(), 'is_standard')) {
                $table->dropColumn(['is_standard']);
            }
            if (Schema::hasColumn($table->getTable(), 'is_template')) {
                $table->dropColumn(['is_template']);
            }
        });
    }
};
