<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MedicalReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MedicalReport
 */
class MedicalReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_id' => $this->visit_id,
            'statut' => $this->statut,
            'statut_label' => $this->statut_label,

            'antecedents_familiaux' => $this->antecedents_familiaux,
            'antecedents_personnels' => $this->antecedents_personnels,

            'poids' => $this->poids,
            'taille' => $this->taille,
            'tension_arterielle' => $this->tension_arterielle,
            'frequence_cardiaque' => $this->frequence_cardiaque,
            'autres_constantes' => $this->autres_constantes,

            'avsc_od' => $this->avsc_od,
            'avsc_og' => $this->avsc_og,
            'avsc_odg' => $this->avsc_odg,
            'avac_od' => $this->avac_od,
            'avac_og' => $this->avac_og,
            'avac_odg' => $this->avac_odg,

            'clinique_etat_general' => $this->clinique_etat_general,
            'clinique_tete_cou' => $this->clinique_tete_cou,
            'clinique_poumons' => $this->clinique_poumons,
            'clinique_coeur' => $this->clinique_coeur,
            'clinique_abdomen' => $this->clinique_abdomen,
            'clinique_membres' => $this->clinique_membres,
            'clinique_autres' => $this->clinique_autres,

            'bio_glycemie' => $this->bio_glycemie,
            'bio_bu' => $this->bio_bu,
            'bio_hbsag' => $this->bio_hbsag,
            'bio_sm' => $this->bio_sm,
            'bio_autres' => $this->bio_autres,

            'img_radio_thorax' => $this->img_radio_thorax,
            'img_autres' => $this->img_autres,

            'examens_speciaux' => $this->examens_speciaux,
            'conclusion' => $this->conclusion,

            'validated_at' => $this->validated_at?->toISOString(),

            'medecin_signataire' => $this->whenLoaded('medecinSignataire', fn () => [
                'id' => $this->medecinSignataire->id,
                'full_name' => $this->medecinSignataire->full_name,
            ]),
            'validated_by' => $this->whenLoaded('validatedBy', fn () => [
                'id' => $this->validatedBy->id,
                'full_name' => $this->validatedBy->full_name,
            ]),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
