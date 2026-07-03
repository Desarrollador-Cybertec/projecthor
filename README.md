# ProjectFlow

Monolito Laravel para gestionar el ciclo de vida completo de proyectos de software con un flujo por etapas comprensible tanto para el equipo de desarrollo como para clientes no técnicos.

## Stack

- Laravel 13 · PHP 8.4
- PostgreSQL (SQLite en desarrollo local)
- Blade + Livewire 3 + Tailwind CSS 4
- Laravel Queues + Scheduler
- Storage compatible con S3
- Pest · Laravel Pint

## Arquitectura

Arquitectura modular por dominios en `app/Domains`:

```
app/Domains/
    Users/          Roles (admin/desarrollador), notificaciones internas
    Projects/       Proyectos, exportación PDF/Excel
    Stages/         Etapas: Planeación → Diseño → Desarrollo → Pruebas → Implementación → Finalizado
    Activities/     Actividades con estados, prioridad, orden y responsables
    Evidence/       Evidencias por actividad (archivos, enlaces, Figma, producción)
    Screenshots/    Capturas de pantalla agrupables por etapa/actividad/versión
    Comments/       Observaciones polimórficas con adjuntos, respuestas y estados
    Files/          Biblioteca documental con versionado
    Timeline/       Línea de tiempo automática por eventos de dominio
    Dashboard/      Indicadores generales
```

Cada dominio contiene sus `Models`, `Enums`, `Services`, `DTOs`, `Policies`, `Events` y `Listeners`. Los componentes Livewire viven en `app/Livewire` y la auditoría transversal en `app/Support/Auditing`.

## Puesta en marcha

```bash
composer install
cp .env.example .env          # configura PostgreSQL, o usa DB_CONNECTION=sqlite
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve             # o Laravel Herd
```

Para colas y tareas programadas en desarrollo:

```bash
php artisan queue:listen
php artisan schedule:work
```

### Credenciales sembradas

| Rol           | Correo                    | Contraseña |
| ------------- | ------------------------- | ---------- |
| Administrador | `admin@projectflow.test`  | `password` |
| Desarrollador | `laura@projectflow.test`  | `password` |

## Funcionalidad

- **Dashboard** con indicadores (proyectos activos/finalizados, actividades pendientes/finalizadas, próximas entregas, última actividad) y tarjetas de proyecto.
- **Proyectos** con cliente, logo, color, responsable, equipo, fechas, prioridad, estado y URLs (producción, pruebas, documentación, repositorio). Al crear un proyecto se generan sus 6 etapas por defecto.
- **Etapas** con objetivo, fechas, estado y avance % (recalculado automáticamente desde sus actividades).
- **Actividades** con prioridad, estado, responsable, orden manual y cambio rápido de estado.
- **Evidencias** con carga múltiple drag & drop, miniaturas para imágenes y enlaces (Figma/producción).
- **Capturas** con vista, módulo, resolución, plataforma y agrupación por etapa/actividad/versión.
- **Archivos** por categoría (contratos, manuales, mockups, branding, actas, documentación técnica, recursos) con versionado, historial, vista previa y descarga autorizada.
- **Observaciones** conversacionales sobre proyectos, etapas, actividades, evidencias y capturas, con adjuntos, respuestas y estados (abierta/en proceso/resuelta).
- **Línea de tiempo** automática vía eventos de dominio.
- **Notificaciones internas** (base de datos, encoladas) y aviso diario de entregas próximas (`projectflow:notify-upcoming-deadlines`, programado a las 08:00).
- **Búsqueda global** (Ctrl+K) sobre proyectos, actividades y archivos visibles.
- **Exportación** a PDF (reporte de proyecto con dompdf) y Excel real `.xlsx` (OpenSpout).
- **Auditoría** de creaciones, cambios y eliminaciones en `audits`.
- **Soft deletes**, políticas de autorización por rol, modo oscuro y diseño responsive.

## Roles

- **Administrador**: acceso total (usuarios, proyectos, etapas, archivos, etc.).
- **Desarrollador**: solo ve y gestiona información de los proyectos donde es responsable o miembro; puede actualizar actividades, cambiar estados, subir evidencias/capturas y crear observaciones.

## Tests

```bash
php artisan test
```

Suite de integración con Pest (autenticación, autorización, CRUD de todos los módulos, subidas de archivos, exportaciones, notificaciones, comando programado y auditoría).
