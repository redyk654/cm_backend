<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Décision d'aptitude prononçable à l'issue d'une visite (référentiel configurable).
 *
 * Les codes sont saisis / stockés en MAJUSCULE.
 * `requires_restriction` signale les décisions qui imposent la saisie d'une
 * restriction (ex. « Apte avec restriction »).
 */
class AptitudeDecision extends BaseModel
{
    use Auditable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'label',
        'requires_restriction',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_restriction' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Ne conserver que les décisions actives.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Trier selon l'ordre d'affichage du référentiel, puis par libellé.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}
