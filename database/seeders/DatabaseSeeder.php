<?php

namespace Database\Seeders;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $pacientes = collect([
            ['nombre' => 'Ana Lopez', 'documento' => 'PAC-001', 'telefono' => '5555-0101', 'email' => 'ana@example.com'],
            ['nombre' => 'Carlos Mendez', 'documento' => 'PAC-002', 'telefono' => '5555-0102', 'email' => 'carlos@example.com'],
            ['nombre' => 'Sofia Ramirez', 'documento' => 'PAC-003', 'telefono' => '5555-0103', 'email' => 'sofia@example.com'],
        ])->map(fn (array $datos): Paciente => Paciente::query()->create($datos));

        $doctores = collect([
            ['nombre' => 'Dra. Elena Morales', 'especialidad' => 'Medicina general', 'numero_colegiado' => 'COL-1001', 'email' => 'elena@clinica.test'],
            ['nombre' => 'Dr. Javier Castillo', 'especialidad' => 'Cardiologia', 'numero_colegiado' => 'COL-1002', 'email' => 'javier@clinica.test'],
            ['nombre' => 'Dra. Lucia Herrera', 'especialidad' => 'Pediatria', 'numero_colegiado' => 'COL-1003', 'email' => 'lucia@clinica.test'],
        ])->map(fn (array $datos): Doctor => Doctor::query()->create($datos));

        $diaBase = Carbon::now()->startOfDay()->next(Carbon::MONDAY);

        Cita::query()->create([
            'paciente_id' => $pacientes[0]->id,
            'doctor_id' => $doctores[0]->id,
            'fecha_hora_inicio' => $diaBase->copy()->setTime(9, 0),
            'fecha_hora_fin' => $diaBase->copy()->setTime(9, 45),
            'motivo' => 'Consulta general y control anual',
            'estado' => EstadoCita::Confirmada,
        ]);

        Cita::query()->create([
            'paciente_id' => $pacientes[1]->id,
            'doctor_id' => $doctores[1]->id,
            'fecha_hora_inicio' => $diaBase->copy()->addDay()->setTime(11, 0),
            'fecha_hora_fin' => $diaBase->copy()->addDay()->setTime(12, 0),
            'motivo' => 'Evaluacion cardiologica',
            'estado' => EstadoCita::Pendiente,
        ]);

        Cita::query()->create([
            'paciente_id' => $pacientes[2]->id,
            'doctor_id' => $doctores[2]->id,
            'fecha_hora_inicio' => $diaBase->copy()->addDays(2)->setTime(14, 0),
            'fecha_hora_fin' => $diaBase->copy()->addDays(2)->setTime(14, 30),
            'motivo' => 'Control pediatrico',
            'estado' => EstadoCita::Atendida,
        ]);
    }
}
