<?php

namespace Tests\Feature;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitasApiTest extends TestCase
{
    use RefreshDatabase;

    private Paciente $paciente;

    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paciente = Paciente::query()->create([
            'nombre' => 'Paciente Prueba',
            'documento' => 'PAC-TEST-01',
            'telefono' => '5555-0001',
            'email' => 'paciente@test.local',
        ]);

        $this->doctor = Doctor::query()->create([
            'nombre' => 'Doctora Prueba',
            'especialidad' => 'Medicina general',
            'numero_colegiado' => 'COL-TEST-01',
            'email' => 'doctora@test.local',
        ]);
    }

    public function test_crea_una_cita_pendiente_con_respuesta_201(): void
    {
        $response = $this->postJson('/api/citas', $this->datosCita());

        $response
            ->assertCreated()
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.paciente.id', $this->paciente->id)
            ->assertJsonPath('data.doctor.id', $this->doctor->id);

        $this->assertDatabaseHas('citas', [
            'doctor_id' => $this->doctor->id,
            'estado' => EstadoCita::Pendiente->value,
            'motivo' => 'Consulta de prueba',
        ]);
    }

    public function test_rechaza_datos_invalidos_antes_de_persistir(): void
    {
        $this->postJson('/api/citas', [
            'paciente_id' => 99999,
            'doctor_id' => $this->doctor->id,
            'fecha_hora_inicio' => 'fecha-invalida',
            'fecha_hora_fin' => '2026-10-01 09:00:00',
            'motivo' => '',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'paciente_id',
            'fecha_hora_inicio',
            'motivo',
        ]);
    }

    public function test_impide_reservas_solapadas_para_el_mismo_doctor(): void
    {
        $this->crearCita();

        $datos = $this->datosCita([
            'fecha_hora_inicio' => '2026-10-01 09:30:00',
            'fecha_hora_fin' => '2026-10-01 10:30:00',
        ]);

        $this->postJson('/api/citas', $datos)
            ->assertConflict()
            ->assertJsonPath(
                'message',
                'El doctor ya tiene una cita activa que se solapa con este horario.'
            );

        $this->assertDatabaseCount('citas', 1);
    }

    public function test_una_cita_cancelada_no_bloquea_el_horario_y_conserva_el_historial(): void
    {
        $cita = $this->crearCita();

        $this->patchJson("/api/citas/{$cita->id}/estado", ['estado' => 'cancelada'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'cancelada');

        $this->postJson('/api/citas', $this->datosCita())->assertCreated();

        $this->assertDatabaseCount('citas', 2);
        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => EstadoCita::Cancelada->value,
        ]);
    }

    public function test_reprograma_una_cita_y_rechaza_un_nuevo_conflicto(): void
    {
        $primera = $this->crearCita();
        $segunda = $this->crearCita([
            'fecha_hora_inicio' => '2026-10-01 11:00:00',
            'fecha_hora_fin' => '2026-10-01 12:00:00',
        ]);

        $this->putJson("/api/citas/{$segunda->id}", [
            'fecha_hora_inicio' => '2026-10-01 09:15:00',
            'fecha_hora_fin' => '2026-10-01 10:15:00',
        ])->assertConflict();

        $this->assertDatabaseHas('citas', [
            'id' => $segunda->id,
            'fecha_hora_inicio' => '2026-10-01 11:00:00',
        ]);

        $this->putJson("/api/citas/{$primera->id}", [
            'fecha_hora_inicio' => '2026-10-02 08:00:00',
            'fecha_hora_fin' => '2026-10-02 09:00:00',
        ])->assertOk()->assertJsonPath('data.id', $primera->id);
    }

    public function test_cambia_estados_validos_y_los_persiste(): void
    {
        $cita = $this->crearCita();

        $this->patchJson("/api/citas/{$cita->id}/estado", ['estado' => 'confirmada'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'confirmada');

        $this->patchJson("/api/citas/{$cita->id}/estado", ['estado' => 'atendida'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'atendida');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => EstadoCita::Atendida->value,
        ]);

        $this->putJson("/api/citas/{$cita->id}", [
            'fecha_hora_inicio' => '2026-10-03 08:00:00',
            'fecha_hora_fin' => '2026-10-03 09:00:00',
        ])->assertUnprocessable();
    }

    public function test_lista_citas_filtradas_por_doctor_paciente_y_rango(): void
    {
        $incluida = $this->crearCita();
        $otroDoctor = Doctor::query()->create([
            'nombre' => 'Otro Doctor',
            'especialidad' => 'Pediatria',
            'numero_colegiado' => 'COL-TEST-02',
        ]);
        $this->crearCita([
            'doctor_id' => $otroDoctor->id,
            'fecha_hora_inicio' => '2026-11-01 09:00:00',
            'fecha_hora_fin' => '2026-11-01 10:00:00',
        ]);

        $this->getJson(
            "/api/citas?doctor_id={$this->doctor->id}&paciente_id={$this->paciente->id}".
            '&desde=2026-10-01T00:00:00&hasta=2026-10-02T00:00:00'
        )->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $incluida->id);
    }

    public function test_permite_filtrar_solo_por_fecha_hasta(): void
    {
        $incluida = $this->crearCita();
        $this->crearCita([
            'fecha_hora_inicio' => '2026-11-01 09:00:00',
            'fecha_hora_fin' => '2026-11-01 10:00:00',
        ]);

        $this->getJson('/api/citas?hasta=2026-10-02T00:00:00')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $incluida->id);
    }

    public function test_expone_catalogos_detalle_y_codigos_404(): void
    {
        $cita = $this->crearCita();

        $this->getJson('/api/doctores')
            ->assertOk()
            ->assertJsonPath('data.0.id', $this->doctor->id);
        $this->getJson('/api/pacientes')
            ->assertOk()
            ->assertJsonPath('data.0.id', $this->paciente->id);
        $this->getJson("/api/citas/{$cita->id}")
            ->assertOk()
            ->assertJsonPath('data.motivo', 'Consulta de prueba');
        $this->getJson('/api/citas/99999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Recurso no encontrado.']);
    }

    private function datosCita(array $cambios = []): array
    {
        return array_merge([
            'paciente_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'fecha_hora_inicio' => '2026-10-01 09:00:00',
            'fecha_hora_fin' => '2026-10-01 10:00:00',
            'motivo' => 'Consulta de prueba',
        ], $cambios);
    }

    private function crearCita(array $cambios = []): Cita
    {
        return Cita::query()->create($this->datosCita($cambios));
    }
}
