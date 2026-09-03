<?php

declare(strict_types=1);

namespace App\Http\Requests\Visit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisitRequest extends FormRequest
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
            'visit_type_id' => ['sometimes', 'required', 'uuid', 'exists:visit_types,id'],
            'date_visite' => ['sometimes', 'required', 'date'],
            'medecin_id' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'visit_type_id.exists' => 'Type de visite introuvable.',
            'medecin_id.exists' => 'Médecin introuvable.',
        ];
    }
}
