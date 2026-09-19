<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agenda Medica</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="/" aria-label="Agenda Medica">
                <span class="brand-mark">AM</span>
                <span><strong>Agenda</strong><small>Centro medico</small></span>
            </a>

            <nav class="primary-nav" aria-label="Navegacion principal">
                <a class="nav-item active" href="#calendario">
                    <span class="nav-icon">01</span> Calendario
                </a>
                <a class="nav-item" href="#resumen">
                    <span class="nav-icon">02</span> Resumen
                </a>
            </nav>

            <div class="sidebar-note">
                <span class="live-dot"></span>
                <div><strong>API conectada</strong><small>Datos en tiempo real</small></div>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Gestion clinica</p>
                    <h1>Agenda de citas</h1>
                </div>
                <div class="topbar-actions">
                    <span class="today-label" id="today-label"></span>
                    <button class="button button-primary" id="new-appointment" type="button">
                        <span>+</span> Nueva cita
                    </button>
                </div>
            </header>

            <section class="stats-grid" id="resumen" aria-label="Resumen de citas">
                <article class="stat-card stat-total">
                    <span class="stat-kicker">En pantalla</span>
                    <strong id="stat-total">0</strong>
                    <small>Citas programadas</small>
                </article>
                <article class="stat-card stat-pending">
                    <span class="stat-kicker">Por gestionar</span>
                    <strong id="stat-pendiente">0</strong>
                    <small>Pendientes</small>
                </article>
                <article class="stat-card stat-confirmed">
                    <span class="stat-kicker">Aseguradas</span>
                    <strong id="stat-confirmada">0</strong>
                    <small>Confirmadas</small>
                </article>
                <article class="stat-card stat-completed">
                    <span class="stat-kicker">Finalizadas</span>
                    <strong id="stat-atendida">0</strong>
                    <small>Atendidas</small>
                </article>
            </section>

            <section class="workspace" id="calendario">
                <div class="workspace-header">
                    <div>
                        <p class="section-label">Planificacion</p>
                        <h2>Calendario clinico</h2>
                    </div>
                    <div class="filters">
                        <label>
                            <span>Doctor</span>
                            <select id="doctor-filter">
                                <option value="">Todos los doctores</option>
                            </select>
                        </label>
                        <label>
                            <span>Paciente</span>
                            <select id="patient-filter">
                                <option value="">Todos los pacientes</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="legend" aria-label="Estados">
                    <span><i class="dot pending"></i>Pendiente</span>
                    <span><i class="dot confirmed"></i>Confirmada</span>
                    <span><i class="dot cancelled"></i>Cancelada</span>
                    <span><i class="dot completed"></i>Atendida</span>
                </div>

                <div class="calendar-wrap">
                    <div id="calendar" aria-label="Calendario de citas"></div>
                </div>
            </section>
        </main>
    </div>

    <dialog class="modal" id="appointment-modal">
        <form id="appointment-form">
            <div class="modal-heading">
                <div>
                    <p class="section-label">Nuevo registro</p>
                    <h2>Programar cita</h2>
                </div>
                <button class="icon-button close-modal" type="button" aria-label="Cerrar">&times;</button>
            </div>
            <div class="form-grid">
                <label class="field">
                    <span>Paciente</span>
                    <select name="paciente_id" id="patient-input" required></select>
                </label>
                <label class="field">
                    <span>Doctor</span>
                    <select name="doctor_id" id="doctor-input" required></select>
                </label>
                <label class="field">
                    <span>Inicio</span>
                    <input name="fecha_hora_inicio" id="start-input" type="datetime-local" required>
                </label>
                <label class="field">
                    <span>Fin</span>
                    <input name="fecha_hora_fin" id="end-input" type="datetime-local" required>
                </label>
                <label class="field field-full">
                    <span>Motivo de consulta</span>
                    <textarea name="motivo" rows="4" minlength="3" maxlength="1000" required
                        placeholder="Describa brevemente el motivo"></textarea>
                </label>
            </div>
            <p class="form-error" id="form-error" role="alert"></p>
            <div class="modal-actions">
                <button class="button button-ghost close-modal" type="button">Cancelar</button>
                <button class="button button-primary" type="submit" id="save-appointment">Guardar cita</button>
            </div>
        </form>
    </dialog>

    <dialog class="modal detail-modal" id="detail-modal">
        <div class="modal-heading">
            <div>
                <p class="section-label">Expediente de cita</p>
                <h2>Detalle</h2>
            </div>
            <button class="icon-button close-detail" type="button" aria-label="Cerrar">&times;</button>
        </div>
        <div class="detail-status" id="detail-status"></div>
        <dl class="detail-list">
            <div><dt>Paciente</dt><dd id="detail-patient"></dd></div>
            <div><dt>Doctor</dt><dd id="detail-doctor"></dd></div>
            <div><dt>Especialidad</dt><dd id="detail-specialty"></dd></div>
            <div><dt>Fecha y hora</dt><dd id="detail-date"></dd></div>
            <div class="detail-wide"><dt>Motivo</dt><dd id="detail-reason"></dd></div>
        </dl>
        <div class="status-actions" id="status-actions">
            <button class="button button-confirm" type="button" data-status="confirmada">Confirmar</button>
            <button class="button button-complete" type="button" data-status="atendida">Marcar atendida</button>
            <button class="button button-danger" type="button" data-status="cancelada">Cancelar cita</button>
        </div>
    </dialog>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>
</body>
</html>
