<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'paciente_id' => $this->paciente_id,
            'doctor_id' => $this->doctor_id,
            'fecha_hora_inicio' => $this->fecha_hora_inicio->toIso8601String(),
            'fecha_hora_fin' => $this->fecha_hora_fin->toIso8601String(),
            'motivo' => $this->motivo,
            'estado' => $this->estado->value,
            'color' => $this->estado->color(),
            'paciente' => new PacienteResource($this->whenLoaded('paciente')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'creada_en' => $this->created_at?->toIso8601String(),
            'actualizada_en' => $this->updated_at?->toIso8601String(),
        ];
    }
}
