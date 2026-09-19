<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'especialidad' => $this->especialidad,
            'numero_colegiado' => $this->numero_colegiado,
            'email' => $this->email,
        ];
    }
}
