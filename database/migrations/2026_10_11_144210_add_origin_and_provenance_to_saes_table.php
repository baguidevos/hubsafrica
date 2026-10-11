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
            $table->string('origin_type')->default('INDEPENDANTE')->after('status');
            $table->string('university_name')->nullable()->after('origin_type');
            $table->string('country')->nullable()->after('address');
            $table->string('city')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saes', function (Blueprint $table) {
            $table->dropColumn(['origin_type', 'university_name', 'country', 'city']);
        });
    }
};
