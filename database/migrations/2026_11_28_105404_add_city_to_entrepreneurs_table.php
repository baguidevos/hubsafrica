<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('entrepreneurs', 'city')) {
            Schema::table('entrepreneurs', function (Blueprint $table): void {
                $table->string('city')->nullable()->after('territory');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('entrepreneurs', 'city')) {
            Schema::table('entrepreneurs', function (Blueprint $table): void {
                $table->dropColumn('city');
            });
        }
    }
};
