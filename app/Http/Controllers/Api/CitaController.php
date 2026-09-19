<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCitaRequest;
use App\Http\Requests\CrearCitaRequest;
use App\Http\Requests\ListarCitasRequest;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use App\Services\CitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CitaController extends Controller
{
    public function __construct(private readonly CitaService $service) {}

    public function index(ListarCitasRequest $request): AnonymousResourceCollection
    {
        return CitaResource::collection($this->service->listar($request->validated()));
    }

    public function store(CrearCitaRequest $request): JsonResponse
    {
        return (new CitaResource($this->service->crear($request->validated())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Cita $cita): CitaResource
    {
        return new CitaResource($cita->load(['paciente', 'doctor']));
    }

    public function update(ActualizarCitaRequest $request, Cita $cita): CitaResource
    {
        return new CitaResource($this->service->actualizar($cita, $request->validated()));
    }
}
