<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Patients (salariés) suivis par le centre médical.
 * Rattachement facultatif à une entreprise ; le rattachement se dénoue si
 * l'entreprise est supprimée (nullOnDelete).
 * Piloté par un modèle héritant de BaseModel : porte created_by / updated_by / deleted_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('numero_dossier')->unique();       // généré : PAT-AAAA-00001
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance')->nullable();
            $table->string('sexe', 10)->nullable();            // M | F | AUTRE
            $table->string('telephone')->nullable();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('poste')->nullable();

            $table->timestamps();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->softDeletes();

            $table->index('nom');
            $table->index('numero_dossier');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
