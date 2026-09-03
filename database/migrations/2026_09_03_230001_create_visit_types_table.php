<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel des types de visite médicale (embauche, périodique, reprise…).
 * Piloté par un modèle héritant de BaseModel : porte created_by / updated_by / deleted_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_types', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code')->unique();          // code métier en MAJUSCULE (ex. EMBAUCHE)
            $table->string('label');                   // libellé affiché
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // ordre d'affichage du référentiel

            $table->timestamps();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->softDeletes();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_types');
    }
};
