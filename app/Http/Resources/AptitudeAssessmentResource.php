<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AptitudeAssessment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AptitudeAssessment
 */
class AptitudeAssessmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_id' => $this->visit_id,
            'aptitude_decision_id' => $this->aptitude_decision_id,
            'decision_code' => $this->decision_code,
            'decision_label' => $this->decision_label,
            'requires_restriction' => $this->requires_restriction,
            'restriction' => $this->restriction,
            'recommandations' => $this->recommandations,
            'duree_validite_mois' => $this->duree_validite_mois,
            'decided_at' => $this->decided_at?->toISOString(),
            'decided_by' => $this->whenLoaded('decidedBy', fn () => [
                'id' => $this->decidedBy->id,
                'full_name' => $this->decidedBy->full_name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
