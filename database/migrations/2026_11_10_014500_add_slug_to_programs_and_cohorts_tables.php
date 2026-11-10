<?php

use App\Models\Cohort;
use App\Models\Program;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('programs', 'slug')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->string('slug', 191)->nullable()->unique()->after('name');
            });
        }

        if (! Schema::hasColumn('cohorts', 'slug')) {
            Schema::table('cohorts', function (Blueprint $table): void {
                $table->string('slug', 191)->nullable()->unique()->after('name');
            });
        }

        foreach (Program::withoutGlobalScopes()->whereNull('slug')->get() as $program) {
            $baseSlug = Str::slug($program->name) ?: 'programme';
            $uniqueSuffix = Str::lower(Str::random(6));
            $program->updateQuietly([
                'slug' => "{$baseSlug}-{$uniqueSuffix}",
            ]);
        }

        foreach (Cohort::withoutGlobalScopes()->whereNull('slug')->get() as $cohort) {
            $baseSlug = Str::slug($cohort->name) ?: 'cohorte';
            $uniqueSuffix = Str::lower(Str::random(6));
            $cohort->updateQuietly([
                'slug' => "{$baseSlug}-{$uniqueSuffix}",
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cohorts', 'slug')) {
            Schema::table('cohorts', function (Blueprint $table): void {
                $table->dropColumn('slug');
            });
        }

        if (Schema::hasColumn('programs', 'slug')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->dropColumn('slug');
            });
        }
    }
};
