<?php

declare(strict_types=1);

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Mise à jour d'un patient. Le numéro de dossier n'est pas modifiable.
 */
class UpdatePatientRequest extends FormRequest
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
            'nom' => ['required', 'string', 'max:150'],
            'prenom' => ['required', 'string', 'max:150'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'sexe' => ['nullable', Rule::in([Patient::SEXE_M, Patient::SEXE_F, Patient::SEXE_AUTRE])],
            'telephone' => ['nullable', 'string', 'max:40'],
            'company_id' => ['nullable', 'uuid', Rule::exists('companies', 'id')->whereNull('deleted_at')],
            'poste' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'sexe.in' => 'Le sexe doit valoir M, F ou AUTRE.',
            'company_id.exists' => "L'entreprise sélectionnée est introuvable.",
        ];
    }
}
