<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Patient (salarié) suivi par le centre médical.
 *
 * `numero_dossier` est généré par le service à la création (PAT-AAAA-00001) et
 * n'est jamais modifié ensuite.
 */
class Patient extends BaseModel
{
    use Auditable;

    /** Sexe : homme. */
    public const SEXE_M = 'M';

    /** Sexe : femme. */
    public const SEXE_F = 'F';

    /** Sexe : autre / non précisé. */
    public const SEXE_AUTRE = 'AUTRE';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'numero_dossier',
        'nom',
        'prenom',
        'date_naissance',
        'sexe',
        'telephone',
        'company_id',
        'poste',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_naissance' => 'date:Y-m-d',
        ];
    }

    /**
     * Entreprise de rattachement (facultative).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Recherche plein-texte simple sur l'identité et le dossier du patient.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term): void {
            $q->where('nom', 'like', "%{$term}%")
                ->orWhere('prenom', 'like', "%{$term}%")
                ->orWhere('numero_dossier', 'like', "%{$term}%")
                ->orWhere('telephone', 'like', "%{$term}%");
        });
    }
}
