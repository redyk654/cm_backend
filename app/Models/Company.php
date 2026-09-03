<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Entreprise cliente : elle envoie ses salariés (patients) en visite médicale.
 */
class Company extends BaseModel
{
    use Auditable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'raison_sociale',
        'telephone',
        'email',
        'adresse',
        'personne_contact',
        'numero_convention',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Salariés rattachés à l'entreprise.
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Recherche plein-texte simple sur les champs identifiants de l'entreprise.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term): void {
            $q->where('raison_sociale', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('personne_contact', 'like', "%{$term}%");
        });
    }
}
