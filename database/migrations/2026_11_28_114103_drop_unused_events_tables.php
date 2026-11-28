<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('event_user');
        Schema::dropIfExists('events');
    }

    public function down(): void
    {
        if (! Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('format')->nullable();
                $table->string('platform')->nullable();
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('ends_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('event_user')) {
            Schema::create('event_user', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('event_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }
};
