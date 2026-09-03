<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visites médicales. `employeur` et `poste` sont figés (snapshot) à la création
 * depuis le dossier du patient : une visite historique garde le contexte de
 * l'époque même si le patient change d'employeur.
 * Piloté par un modèle héritant de BaseModel (created_by / updated_by / deleted_at).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('visit_type_id')->constrained('visit_types')->restrictOnDelete();
            $table->date('date_visite');

            // Snapshot à la création.
            $table->string('employeur')->nullable();
            $table->string('poste')->nullable();

            $table->foreignUuid('medecin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('statut', 20)->default('BROUILLON'); // BROUILLON | EN_COURS | VALIDE

            $table->timestamps();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->softDeletes();

            $table->index('patient_id');
            $table->index('visit_type_id');
            $table->index('statut');
            $table->index('date_visite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
