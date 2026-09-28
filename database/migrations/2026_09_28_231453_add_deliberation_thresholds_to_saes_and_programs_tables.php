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
        Schema::table('saes', function (Blueprint $table) {
            $table->unsignedInteger('default_admission_threshold')->nullable()->default(75)->after('status');
            $table->unsignedInteger('default_waitlist_threshold')->nullable()->default(50)->after('default_admission_threshold');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->unsignedInteger('admission_threshold')->nullable()->after('status');
            $table->unsignedInteger('waitlist_threshold')->nullable()->after('admission_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['admission_threshold', 'waitlist_threshold']);
        });

        Schema::table('saes', function (Blueprint $table) {
            $table->dropColumn(['default_admission_threshold', 'default_waitlist_threshold']);
        });
    }
};
