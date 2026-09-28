<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('BAILLEUR')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('logo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sae_id', 'type']);
        });

        Schema::create('partner_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->decimal('contribution_amount', 15, 2)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->timestamps();

            $table->unique(['program_id', 'partner_id']);
            $table->index(['sae_id', 'program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_program');
        Schema::dropIfExists('partners');
    }
};
