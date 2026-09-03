<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Journal d'audit — append-only (pas d'updated_at, pas de soft delete).
 * Répond à « qui / quoi / quand / avant / après » (§10 du PRD).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type', 20)->default('user'); // user | system

            $table->string('action', 40);                      // CREATE, UPDATE, LOGIN, ...
            $table->string('status', 20)->default('success');  // success | failure
            $table->string('failure_reason', 500)->nullable();

            $table->nullableUuidMorphs('auditable');           // cible : auditable_type + auditable_id

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('description', 500)->nullable();
            $table->json('context')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
