<?php

namespace App\Services;

use App\Enums\EstadoCita;
use App\Exceptions\HorarioNoDisponibleException;
use App\Exceptions\OperacionCitaInvalidaException;
use App\Models\Cita;
use App\Repositories\CitaRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CitaService
{
    public function __construct(private readonly CitaRepository $repository) {}

    public function listar(array $filtros): Collection
    {
        return $this->repository->listar($filtros);
    }

    public function crear(array $datos): Cita
    {
        return DB::transaction(function () use ($datos): Cita {
            $inicio = Carbon::parse($datos['fecha_hora_inicio']);
            $fin = Carbon::parse($datos['fecha_hora_fin']);

            $this->validarDisponibilidad((int) $datos['doctor_id'], $inicio, $fin);

            $datos['estado'] = EstadoCita::Pendiente;

            return $this->repository->crear($datos)->load(['paciente', 'doctor']);
        });
    }

    public function actualizar(Cita $cita, array $datos): Cita
    {
        if (in_array($cita->estado, [EstadoCita::Cancelada, EstadoCita::Atendida], true)) {
            throw new OperacionCitaInvalidaException('Una cita finalizada no puede reprogramarse.');
        }

        return DB::transaction(function () use ($cita, $datos): Cita {
            $doctorId = (int) ($datos['doctor_id'] ?? $cita->doctor_id);
            $inicio = Carbon::parse($datos['fecha_hora_inicio'] ?? $cita->fecha_hora_inicio);
            $fin = Carbon::parse($datos['fecha_hora_fin'] ?? $cita->fecha_hora_fin);

            if ($fin->lessThanOrEqualTo($inicio)) {
                throw new OperacionCitaInvalidaException('La hora de fin debe ser posterior a la hora de inicio.');
            }

            $this->validarDisponibilidad($doctorId, $inicio, $fin, $cita->id);

            return $this->repository->actualizar($cita, $datos)->load(['paciente', 'doctor']);
        });
    }

    public function cambiarEstado(Cita $cita, EstadoCita $nuevoEstado): Cita
    {
        if ($cita->estado === $nuevoEstado) {
            return $cita->load(['paciente', 'doctor']);
        }

        $transiciones = [
            EstadoCita::Pendiente->value => [EstadoCita::Confirmada, EstadoCita::Cancelada],
            EstadoCita::Confirmada->value => [EstadoCita::Atendida, EstadoCita::Cancelada],
            EstadoCita::Cancelada->value => [],
            EstadoCita::Atendida->value => [],
        ];

        if (! in_array($nuevoEstado, $transiciones[$cita->estado->value], true)) {
            throw new OperacionCitaInvalidaException(
                "No se puede cambiar una cita {$cita->estado->value} a {$nuevoEstado->value}."
            );
        }

        return $this->repository
            ->actualizar($cita, ['estado' => $nuevoEstado])
            ->load(['paciente', 'doctor']);
    }

    private function validarDisponibilidad(
        int $doctorId,
        Carbon $inicio,
        Carbon $fin,
        ?int $ignorarCitaId = null,
    ): void {
        if ($this->repository->existeConflicto($doctorId, $inicio, $fin, $ignorarCitaId)) {
            throw new HorarioNoDisponibleException;
        }
    }
}
