<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Certificat d'aptitude : un seul par visite (relation 1-1 via visit_id unique),
 * régénérable. Toutes les données affichées sur le PDF sont figées ici (snapshot) :
 * un certificat historique reste lisible à l'identique même si le patient, la
 * visite ou les référentiels changent ensuite.
 * `reference` (CERT-AAAA-00001) est attribuée à la première génération et
 * conservée lors des régénérations ultérieures.
 * Piloté par un modèle héritant de BaseModel : created_by / updated_by / deleted_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->unique('visit_id');

            $table->string('reference')->unique(); // CERT-<AAAA>-<seq 5 chiffres>

            // Snapshot des données imprimées sur le certificat.
            $table->string('patient_nom');
            $table->string('patient_prenom');
            $table->date('patient_date_naissance')->nullable();
            $table->string('poste')->nullable();
            $table->string('type_visite_label');
            $table->string('decision_label');
            $table->text('restriction')->nullable();
            $table->text('recommandations')->nullable();
            $table->unsignedSmallInteger('duree_validite_mois');
            $table->date('date_examen');
            $table->date('date_expiration');
            $table->string('medecin_nom');
            $table->string('lieu')->default('Douala');

            $table->string('pdf_path')->nullable();

            $table->foreignUuid('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();

            $table->timestamps();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
