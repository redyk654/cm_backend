<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Type de visite médicale (référentiel configurable).
 *
 * Les codes sont saisis / stockés en MAJUSCULE.
 */
class VisitType extends BaseModel
{
    use Auditable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'label',
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
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Ne conserver que les types actifs.
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
