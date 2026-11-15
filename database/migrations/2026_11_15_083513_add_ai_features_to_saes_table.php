<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saes', function (Blueprint $table): void {
            $table->json('ai_features')->nullable()->after('default_waitlist_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('saes', function (Blueprint $table): void {
            $table->dropColumn('ai_features');
        });
    }
};
