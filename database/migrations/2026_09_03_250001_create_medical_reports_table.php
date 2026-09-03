<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rapport médical : un seul par visite (relation 1-1 via visit_id unique).
 * Reprend les rubriques du §6.4 du PRD, toutes facultatives : le rapport se
 * remplit progressivement puis se fige à la validation (statut VALIDE).
 * Piloté par un modèle héritant de BaseModel : created_by / updated_by / deleted_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->unique('visit_id');

            $table->string('statut', 20)->default('BROUILLON'); // BROUILLON | VALIDE

            // Antécédents.
            $table->text('antecedents_familiaux')->nullable();
            $table->text('antecedents_personnels')->nullable();

            // Constantes.
            $table->decimal('poids', 5, 2)->nullable();
            $table->decimal('taille', 5, 2)->nullable();
            $table->string('tension_arterielle')->nullable();
            $table->unsignedSmallInteger('frequence_cardiaque')->nullable();
            $table->string('autres_constantes')->nullable();

            // Acuité visuelle sans correction / avec correction.
            $table->string('avsc_od')->nullable();
            $table->string('avsc_og')->nullable();
            $table->string('avsc_odg')->nullable();
            $table->string('avac_od')->nullable();
            $table->string('avac_og')->nullable();
            $table->string('avac_odg')->nullable();

            // Examen clinique.
            $table->string('clinique_etat_general')->nullable();
            $table->string('clinique_tete_cou')->nullable();
            $table->string('clinique_poumons')->nullable();
            $table->string('clinique_coeur')->nullable();
            $table->string('clinique_abdomen')->nullable();
            $table->string('clinique_membres')->nullable();
            $table->string('clinique_autres')->nullable();

            // Biologie.
            $table->string('bio_glycemie')->nullable();
            $table->string('bio_bu')->nullable();
            $table->string('bio_hbsag')->nullable();
            $table->string('bio_sm')->nullable();
            $table->string('bio_autres')->nullable();

            // Imagerie.
            $table->string('img_radio_thorax')->nullable();
            $table->string('img_autres')->nullable();

            // Examens spéciaux et conclusion.
            $table->text('examens_speciaux')->nullable();
            $table->text('conclusion')->nullable();

            // Médecin signataire du rapport.
            $table->foreignUuid('medecin_signataire_id')->nullable()->constrained('users')->nullOnDelete();

            // Validation.
            $table->timestamp('validated_at')->nullable();
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->softDeletes();

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_reports');
    }
};
