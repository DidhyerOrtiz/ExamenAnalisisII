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
        <header class="app-header">
            <a class="brand" href="/" aria-label="Ir al inicio">
                <span class="brand-symbol" aria-hidden="true"><i></i><b></b></span>
                <span class="brand-copy"><strong>norte</strong><small>centro medico</small></span>
            </a>
            <div class="header-context">
                <span class="connection-state"><i></i> Sistema operativo</span>
                <span class="header-divider" aria-hidden="true"></span>
                <span class="user-chip"><b>DR</b><span>Recepcion</span></span>
            </div>
        </header>

        <main class="main-content">
            <div class="breadcrumb"><span>Recepcion</span><b>/</b><strong>Agenda</strong></div>

            <section class="intro-row">
                <div>
                    <p class="eyebrow">Agenda medica</p>
                    <h1>La consulta, en orden.</h1>
                    <p class="intro-copy">Organiza el dia del equipo y encuentra cada cita sin perder tiempo.</p>
                </div>
                <div class="intro-actions">
                    <span class="today-label" id="today-label"></span>
                    <button class="button button-primary" id="new-appointment" type="button">
                        <span class="button-plus">+</span> Nueva cita
                    </button>
                </div>
            </section>

            <section class="stats-grid" id="resumen" aria-label="Resumen de citas">
                <article class="stat-card stat-total">
                    <div class="stat-top"><span class="stat-kicker">Agenda visible</span><span class="stat-mark">01</span></div>
                    <strong id="stat-total">0</strong>
                    <small>Citas en este rango</small>
                </article>
                <article class="stat-card stat-pending">
                    <div class="stat-top"><span class="stat-kicker">Pendientes</span><span class="stat-mark">02</span></div>
                    <strong id="stat-pendiente">0</strong>
                    <small>Requieren confirmacion</small>
                </article>
                <article class="stat-card stat-confirmed">
                    <div class="stat-top"><span class="stat-kicker">Confirmadas</span><span class="stat-mark">03</span></div>
                    <strong id="stat-confirmada">0</strong>
                    <small>Listas para consulta</small>
                </article>
                <article class="stat-card stat-completed">
                    <div class="stat-top"><span class="stat-kicker">Atendidas</span><span class="stat-mark">04</span></div>
                    <strong id="stat-atendida">0</strong>
                    <small>Historial del rango</small>
                </article>
            </section>

            <section class="content-grid" id="calendario">
                <div class="workspace">
                    <div class="workspace-header">
                        <div>
                            <p class="section-label">Vista de agenda</p>
                            <h2>Calendario de consultas</h2>
                        </div>
                        <div class="filters" aria-label="Filtros de agenda">
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
                    <div class="calendar-hint"><span class="hint-icon">i</span> Haz clic en un espacio libre para agendar. Arrastra una cita para reprogramarla.</div>
                    <div class="calendar-wrap">
                        <div id="calendar" aria-label="Calendario de citas"></div>
                    </div>
                </div>

                <aside class="side-panel" aria-label="Ayuda de agenda">
                    <div class="side-panel-heading">
                        <div>
                            <p class="section-label">Lectura rapida</p>
                            <h2>Como leer la agenda</h2>
                        </div>
                        <span class="side-index">A1</span>
                    </div>
                    <p class="side-copy">Cada color representa el momento actual de una cita. Selecciona un evento para consultar su ficha.</p>
                    <div class="legend" aria-label="Estados de las citas">
                        <span><i class="dot pending"></i><b>Pendiente</b><small>Por confirmar</small></span>
                        <span><i class="dot confirmed"></i><b>Confirmada</b><small>Agendada</small></span>
                        <span><i class="dot cancelled"></i><b>Cancelada</b><small>Conservada</small></span>
                        <span><i class="dot completed"></i><b>Atendida</b><small>Finalizada</small></span>
                    </div>
                    <div class="side-note">
                        <span class="note-line"></span>
                        <p>El historial se conserva incluso cuando una cita se cancela.</p>
                    </div>
                    <button class="side-link" type="button" id="new-appointment-secondary">Agendar otra cita <span aria-hidden="true">&#8594;</span></button>
                </aside>
            </section>
        </main>
    </div>

    <dialog class="modal" id="appointment-modal">
        <form id="appointment-form">
            <div class="modal-heading">
                <div>
                    <p class="section-label">Nueva consulta</p>
                    <h2>Programar cita</h2>
                    <p class="modal-subtitle">Completa los datos para reservar un espacio.</p>
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
                    <textarea name="motivo" rows="4" minlength="3" maxlength="1000" required placeholder="Ej. Control general, seguimiento..."></textarea>
                </label>
            </div>
            <p class="form-error" id="form-error" role="alert"></p>
            <div class="modal-actions">
                <button class="button button-ghost close-modal" type="button">Volver</button>
                <button class="button button-primary" type="submit" id="save-appointment">Guardar cita</button>
            </div>
        </form>
    </dialog>

    <dialog class="modal detail-modal" id="detail-modal">
        <div class="modal-heading">
            <div>
                <p class="section-label">Ficha de consulta</p>
                <h2>Detalle de la cita</h2>
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
