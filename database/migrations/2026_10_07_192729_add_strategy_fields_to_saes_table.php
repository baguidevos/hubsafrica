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
            $table->string('website')->nullable()->after('email');
            $table->longText('vision')->nullable()->after('description');
            $table->longText('mission')->nullable()->after('vision');
            $table->longText('objectives')->nullable()->after('mission');
            $table->longText('services')->nullable()->after('objectives');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saes', function (Blueprint $table) {
            $table->dropColumn(['website', 'vision', 'mission', 'objectives', 'services']);
        });
    }
};
