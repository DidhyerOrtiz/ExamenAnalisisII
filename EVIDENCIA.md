# Evidencia de Serie II

Fecha de verificacion: 19 de septiembre de 2026.

## Matriz de trazabilidad

| Requisito | Implementacion | Evidencia |
| --- | --- | --- |
| RQF-01 | Formulario y `POST /api/citas` | Prueba de creacion `201`. |
| RQF-02 | FullCalendar con vistas mes y semana | `resources/js/app.js` y captura del dashboard. |
| RQF-03 | Consulta de solapamiento en servidor | Prueba automatizada y respuesta `409`. |
| RQF-04 | `eventDrop`/`eventResize` y `PUT /api/citas/{id}` | Suite `CitasApiTest`. |
| RQF-05 | Estado `cancelada`, sin borrado fisico | Prueba de historial conservado. |
| RQF-06 | Filtros de doctor, paciente y rango | Prueba de filtros y controles del dashboard. |
| RQF-07 | API de citas y catalogos | `php artisan route:list --path=api`. |
| RQF-08 | Form Requests y llaves foraneas | Prueba de respuesta `422`. |
| RQF-09 | Detalle cargado mediante `GET /api/citas/{id}` | Modal de detalle. |
| RQF-10 | Color proporcionado por el estado | Enum `EstadoCita` y eventos de FullCalendar. |
| RQNF-01 | MySQL 8.4 con volumen nombrado | `docker compose ps` y `docker volume inspect`. |
| RQNF-02 | Entorno reproducible | `docker compose up -d`. |
| RQNF-03 | JSON y codigos HTTP | Pruebas `200`, `201`, `404`, `409` y `422`. |
| RQNF-04 | Presentacion, API, servicio y repositorio | Seccion Arquitectura del README. |
| RQNF-05 | Cuatro features y merges | Historial Git al final de este documento. |
| RQNF-06 | Diseno adaptable | Reglas CSS para escritorio, tablet y movil. |
| RQNF-07 | Conflicto validado en `CitaService` | Prueba HTTP real `409`. |
| RQNF-08 | Evidencias en repositorio | Este archivo. |

## Docker y persistencia

Comando ejecutado:

```bash
docker compose up -d
docker compose ps
docker volume inspect citas_mysql_data
```

Salida verificada:

```text
NAME          IMAGE       SERVICE   STATUS                   PORTS
citas_mysql   mysql:8.4   mysql     Up (healthy)             127.0.0.1:3307->3306/tcp
Volume=citas_mysql_data Mountpoint=/var/lib/docker/volumes/citas_mysql_data/_data
```

La base se consulto dentro del contenedor:

```text
pacientes  3
doctores   3
citas      3
```

## Migraciones y semillas

Comandos ejecutados:

```bash
php artisan migrate:fresh --seed --force
php artisan migrate:status
```

Resultado: las migraciones de `pacientes`, `doctores` y `citas` aparecen con estado `Ran`. La tabla `citas` contiene llaves foraneas, estado, fechas e indice de disponibilidad.

## API real

Lectura de citas contra MySQL en Docker:

```http
GET /api/citas HTTP/1.1
HTTP/1.1 200 OK
Content-Type: application/json
```

Creacion ejecutada:

```json
{
  "paciente_id": 1,
  "doctor_id": 1,
  "fecha_hora_inicio": "2026-09-24T10:00:00-06:00",
  "fecha_hora_fin": "2026-09-24T10:30:00-06:00",
  "motivo": "Prueba HTTP real"
}
```

Resultado:

```text
HTTP 201 Created
estado: pendiente
```

La misma solicitud se repitio sin cambiar el horario:

```text
HTTP 409 Conflict
El doctor ya tiene una cita activa que se solapa con este horario.
```

## Pruebas automatizadas

Comando ejecutado:

```bash
php artisan test
```

Salida:

```text
PASS  Tests\Feature\CitasApiTest
Tests: 10 passed (43 assertions)
```

Casos cubiertos: creacion, validacion, conflictos, cancelacion historica, reprogramacion, estados persistidos, filtros, catalogos, detalle y `404`.

## Calidad y frontend

```text
php vendor/bin/pint --test  -> passed
npm run build               -> built in 1.44s
docker compose config       -> valido
Dashboard HTTP              -> 200
API GET /api/citas          -> 200 JSON
```

## Capturas

Las capturas se almacenan en `docs/evidencias/`:

- `dashboard.png`: calendario cargado desde la API con colores por estado.
- Agregar antes de la entrega una captura del modal de detalle y otra del formulario si el PR requiere evidencia visual adicional.
- La salida de Docker, API y pruebas queda documentada arriba como evidencia reproducible.

## Historial Git

Se trabajo con `develop` como referencia de integracion sincronizada. Cada feature se creo desde el mismo punto estable de `develop`, se cerro mediante un Pull Request dirigido a `main` y despues se avanzo `develop` al merge aprobado.

| Rama | Pull Request | Requisitos principales |
| --- | --- | --- |
| `feature/docker-mysql-schema` | [PR #1](https://github.com/DidhyerOrtiz/ExamenAnalisisII/pull/1) | RQF-01, RQNF-01, RQNF-02 |
| `feature/api-rest-citas` | [PR #2](https://github.com/DidhyerOrtiz/ExamenAnalisisII/pull/2) | RQF-01, RQF-06, RQF-07, RQF-08, RQNF-03, RQNF-04 |
| `feature/validacion-conflictos-estados` | [PR #3](https://github.com/DidhyerOrtiz/ExamenAnalisisII/pull/3) | RQF-03, RQF-05, RQNF-07 |
| `feature/fullcalendar-ui` | [PR #4](https://github.com/DidhyerOrtiz/ExamenAnalisisII/pull/4) | RQF-02, RQF-04, RQF-09, RQF-10, RQNF-06, RQNF-08 |
| `feature/evidencia-final` | [PR #5](https://github.com/DidhyerOrtiz/ExamenAnalisisII/pull/5) | RQNF-05, RQNF-08 |

Comando de evidencia:

```bash
git log --graph --all --decorate --oneline
```

El historial conserva commits descriptivos por feature y cinco commits de merge generados por GitHub en `main`; no se utilizo squash para que la trazabilidad individual permanezca visible.
