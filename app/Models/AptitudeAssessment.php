<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Décision d'aptitude prononcée à l'issue d'une visite. Une seule par visite.
 *
 * `decision_code` / `decision_label` / `requires_restriction` sont un snapshot du
 * référentiel `aptitude_decisions` au moment de la décision ; ils ne sont pas
 * renseignables directement (le service les recopie depuis la décision choisie).
 */
class AptitudeAssessment extends BaseModel
{
    use Auditable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'aptitude_decision_id',
        'restriction',
        'recommandations',
        'duree_validite_mois',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_restriction' => 'boolean',
            'duree_validite_mois' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function aptitudeDecision(): BelongsTo
    {
        return $this->belongsTo(AptitudeDecision::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
