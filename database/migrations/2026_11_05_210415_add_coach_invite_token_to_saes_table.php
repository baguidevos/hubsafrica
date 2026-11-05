<?php

use App\Models\Sae;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('saes', function (Blueprint $table) {
            $table->string('coach_invite_token', 64)->nullable()->unique()->after('slug');
        });

        foreach (Sae::all() as $sae) {
            $sae->updateQuietly([
                'coach_invite_token' => Str::random(20),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saes', function (Blueprint $table) {
            $table->dropColumn('coach_invite_token');
        });
    }
};
