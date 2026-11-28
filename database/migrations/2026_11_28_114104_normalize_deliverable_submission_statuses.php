<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = [
            'SOUMISE' => 'SOUMIS',
            'EN_EXAMEN' => 'EN_EVALUATION',
            'VALIDEE' => 'VALIDE',
            'NON_VALIDE' => 'REJETEE',
        ];

        foreach ($mapping as $legacy => $canonical) {
            DB::table('deliverable_submissions')
                ->where('status', $legacy)
                ->update(['status' => $canonical]);
        }
    }

    public function down(): void
    {
        // Data normalization is intentionally irreversible without loss of original legacy aliases.
    }
};
