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
            'employeur' => $this->employeur,
            'poste' => $this->poste,
            'type_visite_label' => $this->type_visite_label,
            'type_visite_code' => $this->type_visite_code,
            'date_precedente_visite_periodique' => $this->date_precedente_visite_periodique?->format('Y-m-d'),
            'decision_label' => $this->decision_label,
            'restriction' => $this->restriction,
            'recommandations' => $this->recommandations,
            'duree_validite_mois' => $this->duree_validite_mois,
            'date_examen' => $this->date_examen?->format('Y-m-d'),
            'date_expiration' => $this->date_expiration?->format('Y-m-d'),
            'medecin_nom' => $this->medecin_nom,
            'lieu' => $this->lieu,

            'centre' => [
                'raison_sociale' => (string) config('centre.raison_sociale'),
                'slogan' => (string) config('centre.slogan'),
                'lieu_medecine_travail' => (string) config('centre.lieu_medecine_travail'),
                'legal' => [
                    'forme_capital' => (string) config('centre.legal.forme_capital'),
                    'siege' => (string) config('centre.legal.siege'),
                    'rccm' => (string) config('centre.legal.rccm'),
                    'contribuable' => (string) config('centre.legal.contribuable'),
                    'regime' => (string) config('centre.legal.regime'),
                    'tel' => (string) config('centre.legal.tel'),
                    'email' => (string) config('centre.legal.email'),
                ],
            ],

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
