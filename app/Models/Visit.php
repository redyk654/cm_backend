<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Visite médicale : un événement daté rattaché à un patient et à un type de visite.
 */
class Visit extends BaseModel
{
    use Auditable;

    public const STATUT_BROUILLON = 'BROUILLON';

    public const STATUT_EN_COURS = 'EN_COURS';

    public const STATUT_VALIDE = 'VALIDE';

    public const STATUTS = [
        self::STATUT_BROUILLON,
        self::STATUT_EN_COURS,
        self::STATUT_VALIDE,
    ];

    protected $fillable = [
        'patient_id',
        'visit_type_id',
        'date_visite',
        'employeur',
        'poste',
        'medecin_id',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_visite' => 'date:Y-m-d',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visitType(): BelongsTo
    {
        return $this->belongsTo(VisitType::class);
    }

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_VALIDE => 'Validé',
            default => $this->statut,
        };
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereHas('patient', function (Builder $q) use ($term): void {
            $q->where('nom', 'like', "%{$term}%")
                ->orWhere('prenom', 'like', "%{$term}%")
                ->orWhere('numero_dossier', 'like', "%{$term}%");
        });
    }
}
