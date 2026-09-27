<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_room_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('entrepreneur_id')->constrained('entrepreneurs')->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable(); // ADMINISTRATIF | JURIDIQUE | FINANCE | COMMERCIAL | TECHNIQUE | RH | AUTRE
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->text('comment')->nullable();
            $table->string('uploaded_by_role')->default('ENTREPRENEUR'); // SAE | ENTREPRENEUR
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('RECU'); // RECU | VALIDE | A_CORRIGER
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sae_id', 'entrepreneur_id']);
            $table->index(['sae_id', 'status']);
        });

        Schema::create('form_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->unsignedBigInteger('form_id'); // Réfère form_builder_forms.id
            $table->foreignId('entrepreneur_id')->constrained('entrepreneurs')->cascadeOnDelete();
            $table->foreignId('unlocked_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['form_id', 'entrepreneur_id']);
            $table->index(['sae_id', 'entrepreneur_id']);
        });

        Schema::create('form_data_room_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('to_sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('entrepreneur_id')->constrained('entrepreneurs')->cascadeOnDelete();
            $table->foreignId('shared_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['from_sae_id', 'to_sae_id', 'entrepreneur_id'], 'fdrs_from_to_ent_unique');
        });

        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->constrained('saes')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('entrepreneur_id')->nullable()->constrained('entrepreneurs')->nullOnDelete();
            $table->string('type'); // STATUT | NIVEAU | CANDIDATURE | LIVRABLE | SEANCE | FINANCEMENT | NOTE | ABANDON | RECLAMATION
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['sae_id', 'project_id']);
            $table->index(['sae_id', 'created_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sae_id')->nullable()->constrained('saes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('action'); // CREATE | UPDATE | DELETE | LOGIN | LOGOUT | BATCH_REVIEW | PASSWORD_CHANGE | S_ET_E_COMPUTE
            $table->string('entity'); // Project | Application | Coach | Entrepreneur | etc.
            $table->string('entity_id')->nullable();
            $table->text('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['sae_id', 'action']);
            $table->index(['sae_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('timeline_events');
        Schema::dropIfExists('form_data_room_shares');
        Schema::dropIfExists('form_unlocks');
        Schema::dropIfExists('data_room_documents');
    }
};
