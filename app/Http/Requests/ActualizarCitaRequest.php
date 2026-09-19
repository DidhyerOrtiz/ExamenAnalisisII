<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['sometimes', 'required', 'integer', 'exists:pacientes,id'],
            'doctor_id' => ['sometimes', 'required', 'integer', 'exists:doctores,id'],
            'fecha_hora_inicio' => ['required_with:fecha_hora_fin', 'date'],
            'fecha_hora_fin' => ['required_with:fecha_hora_inicio', 'date', 'after:fecha_hora_inicio'],
            'motivo' => ['sometimes', 'required', 'string', 'min:3', 'max:1000'],
        ];
    }
}
