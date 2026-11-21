<?php

use App\Models\Form;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('form_builder_forms')
            ->where(function ($query): void {
                $query->whereNull('redirect_url')
                    ->orWhere('redirect_url', '');
            })
            ->update([
                'redirect_url' => Form::DEFAULT_REDIRECT_URL,
            ]);
    }

    public function down(): void
    {
        // Forward-only data backfill: existing custom redirect URLs are preserved.
    }
};
