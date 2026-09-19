# Agenda Medica

Modulo de control de citas medicas desarrollado con Laravel 12, FullCalendar 6 y MySQL 8.4 en Docker. Permite programar, consultar, filtrar, reprogramar y cancelar citas sin eliminar su historial.

## Requisitos

- PHP 8.2 o superior con `pdo_mysql` habilitado.
- Composer 2.
- Node.js 20 o superior.
- Docker Desktop con Docker Compose.

MySQL no debe instalarse localmente. El unico motor utilizado por la aplicacion se ejecuta en el servicio `mysql` de `docker-compose.yml`.

## Instalacion

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
docker compose up -d
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Abra `http://localhost:8000`. MySQL queda publicado en `127.0.0.1:3307` y sus datos se conservan en el volumen nombrado `citas_mysql_data`.

Para desarrollo del frontend puede ejecutar `npm run dev` en otra terminal.

## Pruebas

```bash
php artisan test
php vendor/bin/pint --test
npm run build
docker compose config
```

La suite automatizada usa una base efimera y aislada. La verificacion de integracion documentada en `EVIDENCIA.md` se ejecuta contra MySQL 8.4 dentro de Docker.

## API REST

| Metodo | Ruta | Proposito |
| --- | --- | --- |
| `GET` | `/api/citas` | Lista y filtra por `doctor_id`, `paciente_id`, `desde` y `hasta`. |
| `POST` | `/api/citas` | Crea una cita pendiente. |
| `GET` | `/api/citas/{id}` | Devuelve el detalle. |
| `PUT` | `/api/citas/{id}` | Actualiza o reprograma una cita activa. |
| `PATCH` | `/api/citas/{id}/estado` | Confirma, cancela o marca como atendida. |
| `GET` | `/api/doctores` | Lista doctores. |
| `GET` | `/api/pacientes` | Lista pacientes. |

Todos los endpoints responden JSON. Los codigos principales son `200`, `201`, `404`, `409` y `422`.

## Reglas de negocio

- Son citas activas las que tienen estado `pendiente`, `confirmada` o `atendida`.
- Una cita activa no puede solaparse con otra del mismo doctor.
- La validacion de disponibilidad se ejecuta dentro de una transaccion en el servidor.
- Las citas `cancelada` y `atendida` no pueden reprogramarse.
- Cancelar es una baja logica; el registro nunca se elimina de la base de datos.
- Transiciones permitidas: `pendiente -> confirmada|cancelada` y `confirmada -> atendida|cancelada`.

Dos intervalos se solapan cuando `inicio_existente < fin_nuevo` y `fin_existente > inicio_nuevo`. Por eso dos citas consecutivas, donde una termina exactamente cuando comienza la otra, son validas.

## Arquitectura

- `resources/views` y `resources/js`: presentacion y FullCalendar.
- `app/Http/Controllers/Api`: transporte HTTP sin reglas de negocio.
- `app/Http/Requests`: autorizacion y validacion de entradas.
- `app/Http/Resources`: representacion JSON estable.
- `app/Services/CitaService.php`: disponibilidad, estados y transacciones.
- `app/Repositories/CitaRepository.php`: consultas y persistencia.
- `app/Models`: entidades y relaciones Eloquent.
- `database/migrations` y `database/seeders`: esquema reproducible y datos iniciales.

## Datos iniciales

`php artisan migrate:fresh --seed` crea tres pacientes, tres doctores y tres citas de ejemplo en fechas cercanas a la ejecucion. Estos registros provienen de MySQL a traves de la API; no estan codificados en FullCalendar.

## Estados y colores

| Estado | Color |
| --- | --- |
| Pendiente | Ambar |
| Confirmada | Verde azulado |
| Cancelada | Rojo |
| Atendida | Indigo |

Consulte `EVIDENCIA.md` para la trazabilidad de requisitos y los resultados de verificacion.
