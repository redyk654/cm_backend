<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Champs supplémentaires du certificat, pour coller au formulaire papier :
 * - employeur : « … a subi un bilan médical au compte de {employeur} »
 * - type_visite_code : coche la bonne case de la grille « Nature de l'examen »
 * - date_precedente_visite_periodique : mention « Date de la précédente visite périodique »
 * Tous snapshot, nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('employeur')->nullable()->after('patient_date_naissance');
            $table->string('type_visite_code', 50)->nullable()->after('type_visite_label');
            $table->date('date_precedente_visite_periodique')->nullable()->after('type_visite_code');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['employeur', 'type_visite_code', 'date_precedente_visite_periodique']);
        });
    }
};
