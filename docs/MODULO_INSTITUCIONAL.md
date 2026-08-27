# Módulo Cliente Institucional B2B

Portal empresarial para responsables de convenios (RR.HH., bienestar corporativo, instituciones educativas).

## Rol y acceso

| Rol | Portal | Descripción |
|-----|--------|-------------|
| `RESPONSABLE_INSTITUCIONAL` | `/institucional` | Gestiona su institución asignada |
| `CLIENTE_INSTITUCIONAL` | `/cliente` | Empleado/miembro del convenio |
| `ADMIN` | `/institucional` + `/admin` | Puede operar cualquier institución (selector en sidebar) |

**Demo:** `php artisan institucional:crear-demo`  
Credenciales: `rrhh@campoalegre.demo.figuefit.test` / `demo12345`

## Matriz de trazabilidad

| Requisito (RF) | Función | Tablas | Controlador | Vista |
|----------------|---------|--------|-------------|-------|
| RF-054 Convenios | Perfil + plan | `instituciones`, `planes` | `PerfilController`, `PlanController` | `perfil/show`, `plan/index` |
| RF-055 Enlaces registro | Invitaciones + enlaces | `enlaces_registro_institucional`, `invitaciones_institucionales` | `InvitacionController`, `RegistroInstitucionalController` | `invitaciones/index`, `registro-institucional/form` |
| Dashboard B2B | KPIs y actividad | `clientes`, `instituciones` | `DashboardController` | `dashboard` |
| Usuarios vinculados | Miembros del convenio | `clientes`, `usuarios` | `UsuarioController` | `usuarios/index`, `usuarios/show` |
| Reportes | Participación y export | `registros_progreso`, `clientes` | `ReporteController` | `reportes/index` |
| Comunicación | Comunicados | `comunicados_institucionales`, `notificaciones` | `ComunicacionController` | `comunicacion/index` |
| Permisos | Aislamiento por institución | `instituciones.id_user_responsable` | Middleware `role` + `ResolvesInstitucionContext` | — |

## Arquitectura

```
instituciones (+ id_user_responsable, logo, tipo)
    ↓
InstitucionalHubService / InstitucionalRegistroService
    ↓
Controllers/Institucional/*
    ↓
routes/institucional.php  (/institucional/*)
    ↓
layouts/institucional.blade.php + institucional-ds.css (in-*)
```

## Rutas principales

- `GET /institucional` — Dashboard
- `GET /institucional/perfil` — Perfil institución
- `GET /institucional/usuarios` — Miembros vinculados
- `GET /institucional/invitaciones` — Invitaciones y enlaces
- `GET /institucional/plan` — Plan contratado
- `GET /institucional/reportes` — Analítica + export CSV
- `GET /institucional/comunicacion` — Centro de comunicación
- `GET /registro-institucional/enlace/{token}` — Alta pública (enlace grupal)
- `GET /registro-institucional/invitacion/{token}` — Alta pública (invitación individual)

## Tests

```bash
php artisan test tests/Feature/Institucional/InstitucionalModuleTest.php
```

## Integraciones

- **Admin:** CRUD instituciones en `/admin/instituciones`
- **Comercial:** planes `INSTITUCIONAL` / `AMBOS`
- **Notificaciones:** `NotificacionService` para comunicados in-app
- **Login:** redirección automática a `/institucional` para `RESPONSABLE_INSTITUCIONAL`
