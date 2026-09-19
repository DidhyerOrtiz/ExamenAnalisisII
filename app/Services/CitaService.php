<?php

namespace App\Services;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Repositories\CitaRepository;
use Illuminate\Database\Eloquent\Collection;

class CitaService
{
    public function __construct(private readonly CitaRepository $repository) {}

    public function listar(array $filtros): Collection
    {
        return $this->repository->listar($filtros);
    }

    public function crear(array $datos): Cita
    {
        $datos['estado'] = EstadoCita::Pendiente;

        return $this->repository->crear($datos)->load(['paciente', 'doctor']);
    }

    public function actualizar(Cita $cita, array $datos): Cita
    {
        return $this->repository->actualizar($cita, $datos)->load(['paciente', 'doctor']);
    }

    public function cambiarEstado(Cita $cita, EstadoCita $nuevoEstado): Cita
    {
        return $this->repository
            ->actualizar($cita, ['estado' => $nuevoEstado])
            ->load(['paciente', 'doctor']);
    }
}
