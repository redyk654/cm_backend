<?php

declare(strict_types=1);

namespace App\Http\Requests\AptitudeAssessment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Enregistrement de la décision d'aptitude d'une visite.
 * La restriction n'est exigée que pour les décisions qui l'imposent : ce
 * contrôle est fait par le service (il dépend du référentiel).
 */
class SaveAptitudeAssessmentRequest extends FormRequest
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
        return [
            'aptitude_decision_id' => ['required', 'uuid', 'exists:aptitude_decisions,id'],
            'restriction' => ['nullable', 'string'],
            'recommandations' => ['nullable', 'string'],
            'duree_validite_mois' => ['nullable', 'integer', 'min:1', 'max:60'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'aptitude_decision_id.required' => 'La décision d\'aptitude est obligatoire.',
            'aptitude_decision_id.exists' => 'Décision d\'aptitude introuvable.',
            'duree_validite_mois.min' => 'La durée de validité doit être d\'au moins 1 mois.',
            'duree_validite_mois.max' => 'La durée de validité ne peut pas dépasser 60 mois.',
        ];
    }
}
