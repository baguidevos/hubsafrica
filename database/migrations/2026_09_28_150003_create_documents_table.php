<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->morphs('documentable');
            $table->string('category')->default('AUTRE')->index();
            $table->string('title')->nullable();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sae_id', 'category']);
            $table->index(['documentable_type', 'documentable_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
