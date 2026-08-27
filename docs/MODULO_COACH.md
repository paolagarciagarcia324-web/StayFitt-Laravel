# Módulo Coach — Trazabilidad y arquitectura

## Matriz de trazabilidad

| Requisito / CU | Función Coach | Tablas | Controlador | Vista |
|----------------|---------------|--------|-------------|-------|
| CU-03 Asignación coach | Ver clientes asignados | `clientes`, `coaches`, `usuarios` | `ClienteController` | `coach/clientes/*` |
| CU-10–15 Entrenamiento | CRUD planes y rutinas | `planes_entrenamiento`, `rutinas`, `rutina_ejercicios`, `ejercicios` | `EntrenamientoController` | `coach/entrenamiento/*` |
| CU-16–20 Videos | Biblioteca y clases | `videos`, `categorias_video` | `VideoBibliotecaController`, `VideoClaseController` | `coach/videos/*` |
| CU-21–27 Progreso | Seguimiento + observaciones | `registros_progreso` | `ReporteProgresoController` | `coach/reportes/progreso` |
| CU-36 Comunicación | Chat coach-cliente | `chats`, `mensajes`, `chat_participantes` | `ComunicacionController` | `coach/comunicacion/*` |
| CU-40–45 Agenda | Programar sesiones | `sesiones` | `AgendaController` | `coach/agenda/index` |
| CU-46 Reportes | Export CSV progreso | `registros_progreso` | `ReporteProgresoController` | `coach/reportes/progreso` |
| Perfil profesional | Bio, especialidad, foto | `coaches`, `usuarios` | `PerfilController` | `coach/perfil/show` |
| Dashboard operativo | KPIs y actividad | múltiple | `DashboardController` + `CoachHubService` | `coach/dashboard` |
| Propuestas plan | Wizard editorial | `propuestas_plan` | `PropuestaPlanController` | `coach/propuestas/*` |

## Arquitectura

```
Base de datos → Modelos → Servicios → Controladores → Rutas → Vistas → Tests
```

Servicios clave: `CoachHubService`, `EntrenamientoCoachService`, `ChatService`.

Rutas: `routes/coach.php` — middleware `auth` + `role:COACH,ASISTENTE_TECNICO,ADMIN`.

UI: `public/css/coach-ds.css`, `layouts/coach.blade.php`, componentes `x-coach.*`.

## Permisos

- **COACH**: solo clientes con `id_coach` o `id_coach_asignado` propio.
- **ADMIN**: acceso completo al ecosistema.
- **CLIENTE**: 403 en `/coach/*`.

## Verificación

```bash
php artisan test tests/Feature/Coach/CoachModuleTest.php
```
