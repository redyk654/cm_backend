<?php

declare(strict_types=1);

namespace App\Http\Requests\MedicalReport;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Enregistrement du brouillon du rapport médical (§6.4 du PRD).
 *
 * Tous les champs sont facultatifs : le rapport se remplit progressivement.
 * Aucune longueur minimale sur les champs de saisie libre.
 */
class SaveMedicalReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $shortText = ['sometimes', 'nullable', 'string', 'max:255'];
        $longText = ['sometimes', 'nullable', 'string'];

        return [
            'antecedents_familiaux' => $longText,
            'antecedents_personnels' => $longText,

            'poids' => ['sometimes', 'nullable', 'numeric'],
            'taille' => ['sometimes', 'nullable', 'numeric'],
            'tension_arterielle' => $shortText,
            'frequence_cardiaque' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:400'],
            'autres_constantes' => $shortText,

            'avsc_od' => $shortText,
            'avsc_og' => $shortText,
            'avsc_odg' => $shortText,
            'avac_od' => $shortText,
            'avac_og' => $shortText,
            'avac_odg' => $shortText,

            'clinique_etat_general' => $shortText,
            'clinique_tete_cou' => $shortText,
            'clinique_poumons' => $shortText,
            'clinique_coeur' => $shortText,
            'clinique_abdomen' => $shortText,
            'clinique_membres' => $shortText,
            'clinique_autres' => $shortText,

            'bio_glycemie' => $shortText,
            'bio_bu' => $shortText,
            'bio_hbsag' => $shortText,
            'bio_sm' => $shortText,
            'bio_autres' => $shortText,

            'img_radio_thorax' => $shortText,
            'img_autres' => $shortText,

            'examens_speciaux' => $longText,
            'conclusion' => $longText,

            'medecin_signataire_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'poids.numeric' => 'Le poids doit être un nombre.',
            'taille.numeric' => 'La taille doit être un nombre.',
            'frequence_cardiaque.integer' => 'La fréquence cardiaque doit être un entier.',
            'medecin_signataire_id.exists' => 'Médecin signataire introuvable.',
        ];
    }
}
