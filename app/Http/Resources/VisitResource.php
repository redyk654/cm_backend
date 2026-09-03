<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Visit
 */
class VisitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date_visite' => $this->date_visite?->format('Y-m-d'),
            'statut' => $this->statut,
            'statut_label' => $this->statut_label,
            'employeur' => $this->employeur,
            'poste' => $this->poste,
            'patient_id' => $this->patient_id,
            'visit_type_id' => $this->visit_type_id,
            'medecin_id' => $this->medecin_id,
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient->id,
                'nom' => $this->patient->nom,
                'prenom' => $this->patient->prenom,
                'numero_dossier' => $this->patient->numero_dossier,
            ]),
            'visit_type' => VisitTypeResource::make($this->whenLoaded('visitType')),
            'medecin' => $this->whenLoaded('medecin', fn () => [
                'id' => $this->medecin->id,
                'full_name' => $this->medecin->full_name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
