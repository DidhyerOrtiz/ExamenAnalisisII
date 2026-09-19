<?php

namespace App\Repositories;

use App\Enums\EstadoCita;
use App\Models\Cita;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class CitaRepository
{
    public function listar(array $filtros): Collection
    {
        return Cita::query()
            ->with(['paciente', 'doctor'])
            ->when($filtros['doctor_id'] ?? null, fn ($query, $doctorId) => $query->where('doctor_id', $doctorId))
            ->when($filtros['paciente_id'] ?? null, fn ($query, $pacienteId) => $query->where('paciente_id', $pacienteId))
            ->when($filtros['desde'] ?? null, fn ($query, $desde) => $query->where(
                'fecha_hora_fin',
                '>',
                Carbon::parse($desde),
            ))
            ->when($filtros['hasta'] ?? null, fn ($query, $hasta) => $query->where(
                'fecha_hora_inicio',
                '<',
                Carbon::parse($hasta),
            ))
            ->orderBy('fecha_hora_inicio')
            ->get();
    }

    public function existeConflicto(
        int $doctorId,
        Carbon $inicio,
        Carbon $fin,
        ?int $ignorarCitaId = null,
    ): bool {
        return Cita::query()
            ->where('doctor_id', $doctorId)
            ->where('estado', '!=', EstadoCita::Cancelada->value)
            ->where('fecha_hora_inicio', '<', $fin)
            ->where('fecha_hora_fin', '>', $inicio)
            ->when($ignorarCitaId, fn ($query) => $query->whereKeyNot($ignorarCitaId))
            ->lockForUpdate()
            ->exists();
    }

    public function crear(array $datos): Cita
    {
        return Cita::query()->create($datos);
    }

    public function actualizar(Cita $cita, array $datos): Cita
    {
        $cita->update($datos);

        return $cita;
    }
}
