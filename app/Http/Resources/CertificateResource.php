<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Certificate
 */
class CertificateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_id' => $this->visit_id,
            'reference' => $this->reference,

            'patient_nom' => $this->patient_nom,
            'patient_prenom' => $this->patient_prenom,
            'patient_date_naissance' => $this->patient_date_naissance?->format('Y-m-d'),
            'poste' => $this->poste,
            'type_visite_label' => $this->type_visite_label,
            'decision_label' => $this->decision_label,
            'restriction' => $this->restriction,
            'recommandations' => $this->recommandations,
            'duree_validite_mois' => $this->duree_validite_mois,
            'date_examen' => $this->date_examen?->format('Y-m-d'),
            'date_expiration' => $this->date_expiration?->format('Y-m-d'),
            'medecin_nom' => $this->medecin_nom,
            'lieu' => $this->lieu,

            'generated_at' => $this->generated_at?->toISOString(),
            'generated_by' => $this->whenLoaded('generatedBy', fn () => [
                'id' => $this->generatedBy->id,
                'full_name' => $this->generatedBy->full_name,
            ]),

            'download_url' => '/api/visits/'.$this->visit_id.'/certificate/download',

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
