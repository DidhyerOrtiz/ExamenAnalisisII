import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import esLocale from '@fullcalendar/core/locales/es';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

const calendarElement = document.getElementById('calendar');

if (calendarElement) {
    const modal = document.getElementById('appointment-modal');
    const detailModal = document.getElementById('detail-modal');
    const form = document.getElementById('appointment-form');
    const errorBox = document.getElementById('form-error');
    const saveButton = document.getElementById('save-appointment');
    const toast = document.getElementById('toast');
    const doctorFilter = document.getElementById('doctor-filter');
    const patientFilter = document.getElementById('patient-filter');
    let selectedAppointmentId = null;

    const formatDateTime = new Intl.DateTimeFormat('es-GT', {
        dateStyle: 'long',
        timeStyle: 'short',
    });

    const showToast = (message, type = 'success') => {
        toast.textContent = message;
        toast.className = `toast visible ${type}`;
        window.setTimeout(() => toast.classList.remove('visible'), 3200);
    };

    const getErrorMessage = (error) => {
        const errors = error.response?.data?.errors;
        if (errors) return Object.values(errors).flat()[0];

        return error.response?.data?.message || 'No fue posible completar la operacion.';
    };

    const toInputValue = (date) => {
        const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
        return local.toISOString().slice(0, 16);
    };

    const openCreateModal = (start = new Date()) => {
        const normalizedStart = new Date(start);
        if (normalizedStart.getHours() === 0 && normalizedStart.getMinutes() === 0) {
            normalizedStart.setHours(9, 0, 0, 0);
        }
        const end = new Date(normalizedStart.getTime() + 30 * 60000);

        form.reset();
        errorBox.textContent = '';
        document.getElementById('start-input').value = toInputValue(normalizedStart);
        document.getElementById('end-input').value = toInputValue(end);
        document.getElementById('doctor-input').value = doctorFilter.value;
        document.getElementById('patient-input').value = patientFilter.value;
        modal.showModal();
    };

    const updateStats = (appointments) => {
        const totals = appointments.reduce((counts, item) => {
            counts[item.estado] = (counts[item.estado] || 0) + 1;
            return counts;
        }, {});

        document.getElementById('stat-total').textContent = appointments.length;
        document.getElementById('stat-pendiente').textContent = totals.pendiente || 0;
        document.getElementById('stat-confirmada').textContent = totals.confirmada || 0;
        document.getElementById('stat-atendida').textContent = totals.atendida || 0;
    };

    const toCalendarEvent = (appointment) => ({
        id: String(appointment.id),
        title: `${appointment.paciente.nombre} · ${appointment.doctor.nombre}`,
        start: appointment.fecha_hora_inicio,
        end: appointment.fecha_hora_fin,
        backgroundColor: appointment.color,
        borderColor: appointment.color,
        extendedProps: appointment,
    });

    const loadEvents = async (info, success, failure) => {
        try {
            const params = { desde: info.startStr, hasta: info.endStr };
            if (doctorFilter.value) params.doctor_id = doctorFilter.value;
            if (patientFilter.value) params.paciente_id = patientFilter.value;

            const response = await axios.get('/api/citas', { params });
            updateStats(response.data.data);
            success(response.data.data.map(toCalendarEvent));
        } catch (error) {
            showToast(getErrorMessage(error), 'error');
            failure(error);
        }
    };

    const showDetail = async (appointmentId) => {
        try {
            const response = await axios.get(`/api/citas/${appointmentId}`);
            const appointment = response.data.data;
            selectedAppointmentId = appointment.id;

            document.getElementById('detail-patient').textContent = appointment.paciente.nombre;
            document.getElementById('detail-doctor').textContent = appointment.doctor.nombre;
            document.getElementById('detail-specialty').textContent = appointment.doctor.especialidad;
            document.getElementById('detail-reason').textContent = appointment.motivo;
            document.getElementById('detail-date').textContent =
                `${formatDateTime.format(new Date(appointment.fecha_hora_inicio))} - ` +
                new Intl.DateTimeFormat('es-GT', { timeStyle: 'short' })
                    .format(new Date(appointment.fecha_hora_fin));

            const status = document.getElementById('detail-status');
            status.textContent = appointment.estado;
            status.className = `detail-status status-${appointment.estado}`;

            document.querySelectorAll('[data-status]').forEach((button) => {
                const target = button.dataset.status;
                button.hidden = !(
                    (appointment.estado === 'pendiente' && ['confirmada', 'cancelada'].includes(target)) ||
                    (appointment.estado === 'confirmada' && ['atendida', 'cancelada'].includes(target))
                );
            });

            detailModal.showModal();
        } catch (error) {
            showToast(getErrorMessage(error), 'error');
        }
    };

    const persistNewDates = async (info) => {
        try {
            await axios.put(`/api/citas/${info.event.id}`, {
                fecha_hora_inicio: info.event.start.toISOString(),
                fecha_hora_fin: info.event.end.toISOString(),
            });
            showToast('Cita reprogramada correctamente.');
            calendar.refetchEvents();
        } catch (error) {
            info.revert();
            showToast(getErrorMessage(error), 'error');
        }
    };

    const calendar = new Calendar(calendarElement, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        locale: esLocale,
        initialView: window.innerWidth < 900 ? 'timeGridWeek' : 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek',
        },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana' },
        allDaySlot: false,
        nowIndicator: true,
        selectable: true,
        editable: true,
        eventDurationEditable: true,
        slotMinTime: '07:00:00',
        slotMaxTime: '20:00:00',
        height: 'auto',
        events: loadEvents,
        dateClick: (info) => openCreateModal(info.date),
        eventClick: (info) => showDetail(info.event.id),
        eventDrop: persistNewDates,
        eventResize: persistNewDates,
        eventAllow: (_dropInfo, event) =>
            ['pendiente', 'confirmada'].includes(event.extendedProps.estado),
        eventDidMount: (info) => {
            info.el.title = `${info.event.title} (${info.event.extendedProps.estado})`;
        },
    });

    const fillSelect = (select, items, label) => {
        select.replaceChildren();
        if (select === doctorFilter || select === patientFilter) {
            select.add(new Option(select === doctorFilter ? 'Todos los doctores' : 'Todos los pacientes', ''));
        } else {
            select.add(new Option(label, ''));
        }
        items.forEach((item) => {
            const text = item.especialidad ? `${item.nombre} · ${item.especialidad}` : item.nombre;
            select.add(new Option(text, item.id));
        });
    };

    const initialize = async () => {
        try {
            const [doctors, patients] = await Promise.all([
                axios.get('/api/doctores'),
                axios.get('/api/pacientes'),
            ]);

            fillSelect(doctorFilter, doctors.data.data);
            fillSelect(document.getElementById('doctor-input'), doctors.data.data, 'Seleccione un doctor');
            fillSelect(patientFilter, patients.data.data);
            fillSelect(document.getElementById('patient-input'), patients.data.data, 'Seleccione un paciente');
            calendar.render();
        } catch (error) {
            showToast('No se pudieron cargar los catalogos de la API.', 'error');
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorBox.textContent = '';
        saveButton.disabled = true;
        saveButton.textContent = 'Guardando...';

        try {
            const payload = Object.fromEntries(new FormData(form));
            await axios.post('/api/citas', payload);
            modal.close();
            calendar.refetchEvents();
            showToast('Cita creada correctamente.');
        } catch (error) {
            errorBox.textContent = getErrorMessage(error);
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Guardar cita';
        }
    });

    document.querySelectorAll('.close-modal').forEach((button) =>
        button.addEventListener('click', () => modal.close()));
    document.querySelectorAll('.close-detail').forEach((button) =>
        button.addEventListener('click', () => detailModal.close()));
    document.getElementById('new-appointment').addEventListener('click', () => openCreateModal());
    doctorFilter.addEventListener('change', () => calendar.refetchEvents());
    patientFilter.addEventListener('change', () => calendar.refetchEvents());

    document.getElementById('status-actions').addEventListener('click', async (event) => {
        const button = event.target.closest('[data-status]');
        if (!button || !selectedAppointmentId) return;

        try {
            await axios.patch(`/api/citas/${selectedAppointmentId}/estado`, {
                estado: button.dataset.status,
            });
            detailModal.close();
            calendar.refetchEvents();
            showToast('Estado actualizado correctamente.');
        } catch (error) {
            showToast(getErrorMessage(error), 'error');
        }
    });

    document.getElementById('today-label').textContent = new Intl.DateTimeFormat('es-GT', {
        weekday: 'long', day: 'numeric', month: 'long',
    }).format(new Date());

    initialize();
}
