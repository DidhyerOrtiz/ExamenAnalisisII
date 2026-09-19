<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListarCitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['nullable', 'integer', 'exists:doctores,id'],
            'paciente_id' => ['nullable', 'integer', 'exists:pacientes,id'],
            'desde' => ['nullable', 'date'],
            'hasta' => [
                'nullable',
                'date',
                Rule::when($this->filled('desde'), ['after:desde']),
            ],
        ];
    }
}
