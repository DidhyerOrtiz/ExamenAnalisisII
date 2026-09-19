<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoCita;
use App\Http\Controllers\Controller;
use App\Http\Requests\CambiarEstadoCitaRequest;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use App\Services\CitaService;

class CambiarEstadoCitaController extends Controller
{
    public function __invoke(
        CambiarEstadoCitaRequest $request,
        Cita $cita,
        CitaService $service,
    ): CitaResource {
        $estado = EstadoCita::from($request->validated('estado'));

        return new CitaResource($service->cambiarEstado($cita, $estado));
    }
}
