<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctores,id'],
            'fecha_hora_inicio' => ['required', 'date'],
            'fecha_hora_fin' => ['required', 'date', 'after:fecha_hora_inicio'],
            'motivo' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }
}
