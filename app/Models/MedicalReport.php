<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rapport médical d'une visite (§6.4 du PRD). Un seul par visite.
 *
 * Cycle de vie : BROUILLON (modifiable) puis VALIDE (figé). Une fois validé, le
 * rapport ne peut plus être modifié — le rectificatif après validation n'est pas
 * traité dans cette version (hypothèse H5).
 */
class MedicalReport extends BaseModel
{
    use Auditable;

    public const STATUT_BROUILLON = 'BROUILLON';

    public const STATUT_VALIDE = 'VALIDE';

    public const STATUTS = [
        self::STATUT_BROUILLON,
        self::STATUT_VALIDE,
    ];

    /**
     * Champs du §6.4 renseignables librement + médecin signataire.
     * Le statut et les colonnes de validation sont pilotés par le service.
     *
     * @var list<string>
     */
    protected $fillable = [
        'antecedents_familiaux',
        'antecedents_personnels',
        'poids',
        'taille',
        'tension_arterielle',
        'frequence_cardiaque',
        'autres_constantes',
        'avsc_od',
        'avsc_og',
        'avsc_odg',
        'avac_od',
        'avac_og',
        'avac_odg',
        'clinique_etat_general',
        'clinique_tete_cou',
        'clinique_poumons',
        'clinique_coeur',
        'clinique_abdomen',
        'clinique_membres',
        'clinique_autres',
        'bio_glycemie',
        'bio_bu',
        'bio_hbsag',
        'bio_sm',
        'bio_autres',
        'img_radio_thorax',
        'img_autres',
        'examens_speciaux',
        'conclusion',
        'medecin_signataire_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'poids' => 'decimal:2',
            'taille' => 'decimal:2',
            'validated_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function medecinSignataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medecin_signataire_id');
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_VALIDE => 'Validé',
            default => (string) $this->statut,
        };
    }

    public function isValidated(): bool
    {
        return $this->statut === self::STATUT_VALIDE;
    }
}
