# Módulo Asistente Técnico (asistentetecnico01)

## Matriz de trazabilidad

| Requisito | Función | Tablas | Controlador | Vista |
|-----------|---------|--------|-------------|-------|
| Apoyo operativo coach | Dashboard KPIs | `clientes`, `propuestas_plan`, `planes_entrenamiento`, `videos` | `Staff\DashboardController` | `staff/dashboard` |
| Perfil ops | Datos + coach asociado | `usuarios`, `coaches` | `Staff\PerfilController` | `staff/perfil/show` |
| Clientes apoyo | Consulta + observaciones | `clientes`, `registros_progreso` | `Staff\ClienteController` | `staff/clientes/*` |
| Rutinas borrador | Crear propuesta inicial | `planes_entrenamiento`, `propuestas_plan` | `Staff\RutinaController` | `staff/rutinas/index` |
| Propuestas | Flujo borrador→revisión | `propuestas_plan`, `programas_virtuales` | `Staff\PropuestaController` + `Coach\PropuestaPlanController` | `staff/propuestas/*` |
| Videos | Biblioteca referencias | `videos`, `categorias_video` | `Staff\VideoBibliotecaController` | `staff/videos/index` |
| Comunicación ops | Notas al coach | `propuestas_plan` | `Staff\ComunicacionController` | `staff/comunicacion/index` |
| Novedades welcome | CRUD existente | `novedades_welcome` | `Staff\NovedadWelcomeController` | `staff/novedades/*` |
| Contexto coach | Selector operativo | `coaches` | `Staff\CoachContextController` | sidebar layout |

## Flujo propuesta

```
Asistente crea borrador (staff/coach propuestas)
    ↓
Completa videos + nutrición (coach.propuestas.*)
    ↓
Envía a PENDIENTE_REVISION
    ↓
Coach / Admin aprueba
    ↓
PUBLICADO → Cliente
```

## Permisos

| Rol | Acceso |
|-----|--------|
| ADMIN | `/staff/*` + admin completo |
| ASISTENTE_TECNICO | `/staff/*`, `/coach/propuestas/*` (apoyo) |
| COACH | `/coach/*` (aprobación final) |
| CLIENTE | 403 en `/staff/*` |

**Restricciones asistente:** no administra usuarios, pagos ni permisos globales.

## Arquitectura

- Servicio: `App\Services\Staff\StaffHubService`
- Trait: `App\Support\ResolvesStaffContext` (coach en sesión `staff_coach_id`)
- UI: `public/css/staff-ds.css` (prefijo `st-*`, body `is-staff`)
- Rutas: `routes/staff.php`

## Demo

```powershell
$env:DB_DATABASE="stayfit"
php artisan staff:crear-demo
```

Credenciales: `asistente@demo.figuefit.test` / `demo12345` → `/staff`

## Tests

```powershell
$env:DB_DATABASE="stayfit_test"
php artisan test tests/Feature/Staff/StaffModuleTest.php
```
