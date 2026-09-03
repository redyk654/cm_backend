<?php

declare(strict_types=1);

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Vérification préalable d'un doublon d'identité patient (endpoint dédié,
 * avant saisie complète du dossier).
 */
class CheckDuplicatePatientRequest extends FormRequest
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
        ];
    }
}
