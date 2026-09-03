<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Certificat d'aptitude médicale d'une visite. Un seul par visite, régénérable.
 *
 * Tous les champs affichés sur le PDF sont figés ici (snapshot) : le service les
 * construit à la génération, ils ne sont pas renseignés par l'utilisateur.
 */
class Certificate extends BaseModel
{
    use Auditable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'patient_date_naissance' => 'date:Y-m-d',
            'date_examen' => 'date:Y-m-d',
            'date_expiration' => 'date:Y-m-d',
            'date_precedente_visite_periodique' => 'date:Y-m-d',
            'duree_validite_mois' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
