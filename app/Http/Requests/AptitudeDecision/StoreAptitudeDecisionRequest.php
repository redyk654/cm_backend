<?php

declare(strict_types=1);

namespace App\Http\Requests\AptitudeDecision;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création d'une décision d'aptitude. Le code est normalisé en MAJUSCULE.
 */
class StoreAptitudeDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper((string) $this->input('code'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'uppercase', Rule::unique('aptitude_decisions', 'code')],
            'label' => ['required', 'string', 'max:150'],
            'requires_restriction' => ['boolean'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code est déjà utilisé par une autre décision d\'aptitude.',
            'label.required' => 'Le libellé est obligatoire.',
        ];
    }
}
