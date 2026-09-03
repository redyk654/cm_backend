<?php

declare(strict_types=1);

namespace App\Http\Requests\Visit;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
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
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'visit_type_id' => ['required', 'uuid', 'exists:visit_types,id'],
            'date_visite' => ['required', 'date'],
            'medecin_id' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Le patient est obligatoire.',
            'patient_id.exists' => 'Patient introuvable.',
            'visit_type_id.required' => 'Le type de visite est obligatoire.',
            'visit_type_id.exists' => 'Type de visite introuvable.',
            'date_visite.required' => 'La date de la visite est obligatoire.',
            'medecin_id.exists' => 'Médecin introuvable.',
        ];
    }
}
