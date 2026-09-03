<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel des décisions d'aptitude prononçables à l'issue d'une visite
 * (apte, apte avec restriction, inaptitude temporaire/définitive…).
 * Piloté par un modèle héritant de BaseModel : porte created_by / updated_by / deleted_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aptitude_decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code')->unique();                     // code métier en MAJUSCULE (ex. APTE)
            $table->string('label');                              // libellé affiché
            $table->boolean('requires_restriction')->default(false); // impose la saisie d'une restriction
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

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
        Schema::dropIfExists('aptitude_decisions');
    }
};
