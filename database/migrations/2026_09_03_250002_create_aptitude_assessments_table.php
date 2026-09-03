<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Décision d'aptitude : une seule par visite (relation 1-1 via visit_id unique).
 * `decision_code` / `decision_label` / `requires_restriction` sont un snapshot du
 * référentiel `aptitude_decisions` au moment de la décision : une décision
 * historique conserve son sens même si le référentiel évolue ensuite.
 * Piloté par un modèle héritant de BaseModel : created_by / updated_by / deleted_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aptitude_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->unique('visit_id');

            $table->foreignUuid('aptitude_decision_id')->constrained('aptitude_decisions')->restrictOnDelete();

            // Snapshot du référentiel au moment de la décision.
            $table->string('decision_code');
            $table->string('decision_label');
            $table->boolean('requires_restriction')->default(false);

            $table->text('restriction')->nullable();
            $table->text('recommandations')->nullable();
            $table->unsignedSmallInteger('duree_validite_mois')->default(12);

            $table->foreignUuid('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aptitude_assessments');
    }
};
