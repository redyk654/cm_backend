<?php

declare(strict_types=1);

namespace App\Http\Requests\MedicalReport;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation (mise au statut VALIDE) du rapport médical.
 * Le médecin signataire est optionnel ici : à défaut, le service reprend celui
 * déjà renseigné sur le rapport, puis l'utilisateur qui valide.
 */
class ValidateMedicalReportRequest extends FormRequest
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
            'medecin_signataire_id' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'medecin_signataire_id.exists' => 'Médecin signataire introuvable.',
        ];
    }
}
