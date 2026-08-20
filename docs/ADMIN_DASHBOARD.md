# Admin Dashboard — explicación y trazabilidad

**Versión:** StayFit v4.0 · Base de datos `stayfit`

## Objetivo
Panel de administración para operar el negocio sobre la BD real (`usuarios`, `planes`, `solicitudes_compra`, `pagos`, `clientes`, etc.), con la línea visual negro/fucsia de FigueFit.

## Cómo entrar
1. Login en `/login` con un usuario `ADMIN` de `usuarios` + `user_roles`
2. Credencial de desarrollo sembrada:
   - Correo: `paola@correo.com`
   - Contraseña: `paola12345`
3. Redirección automática a `/admin`
4. Solo rol `ADMIN` puede ver el panel (403 para otros)

## Cómo se construyó
1. Roles (`ADMIN`, `COACH`, `CLIENTE`, `CLIENTE_INSTITUCIONAL`) y vistas (`vw_dashboard_metricas`, `vw_solicitudes_pendientes`).
2. Menú Admin según el flujo: validar compras → usuarios/clientes → planes → instituciones.
3. Middleware `role:ADMIN` y redirección post-login.
4. Aprobación de solicitudes crea en BD: `usuarios` + rol `CLIENTE` + `clientes` + `planes_cliente` + `acceso_cliente_modulo` + pago `VALIDADO`.

## Módulos del panel

| Módulo | Ruta | Tablas / vistas |
|--------|------|-----------------|
| Dashboard | `/admin` | `vw_dashboard_metricas` |
| Pagos | `/admin/pagos` | `pagos` |
| Tienda | `/admin/tienda` | `productos_tienda`, `ordenes_tienda`, `categorias_producto` |
| Planes | `/admin/planes` | `planes`, `plan_modulos`, `modulos_servicio` |
| Usuarios | `/admin/usuarios` | `usuarios`, `user_roles`, `roles` |
| Clientes | `/admin/clientes` | `clientes`, `usuarios` |
| Coaches | `/admin/coaches` | `coaches`, `usuarios` |
| Instituciones | `/admin/instituciones` | `instituciones` |
| Horarios | `/admin/horarios` | `horarios_presenciales` |
| Pasarelas | `/admin/pasarelas` | `configuracion_pasarelas` |

## Flujo crítico: aprobar solicitud
1. Visitante elige plan → pre-registro (`solicitudes_compra` + `pagos` en `PENDIENTE`).
2. Admin revisa solicitud y comprobante.
3. **Aprobar** (`SolicitudAprobacionService`):
   - Crea/activa `usuarios` (password inicial = documento)
   - Asigna rol `CLIENTE`
   - Crea/activa `clientes`
   - Marca `pagos` como `VALIDADO`
   - Crea `planes_cliente` con estado activo
   - Genera `acceso_cliente_modulo` según módulos del plan
   - Solicitud → `VALIDADA`
4. **Rechazar**: solicitud `RECHAZADA`, pago `RECHAZADO` (libera cupo).

## Base de datos y migraciones
- Conexión: MySQL · BD `stayfit` (`.env`: `DB_DATABASE=stayfit`)
- Esquema v4.0: migraciones `database/migrations/2026_08_16_*.php`
- Migraciones antiguas `2026_03_01_*` están deshabilitadas (no-op)
- Seeds: roles, módulos, pasarelas, planes ejemplo, admin Paola

## Archivos principales
- Middleware: `app/Http/Middleware/EnsureRole.php`
- Rutas: `routes/admin.php`
- Controllers: `app/Http/Controllers/Admin/*`
- Servicio: `app/Services/SolicitudAprobacionService.php`
- Layout/CSS: `resources/views/layouts/admin.blade.php`, `public/css/admin.css`
- Vistas: `resources/views/admin/**`

## Pendiente (siguientes iteraciones)
- Alta completa de coaches desde Admin
- Facturas / reportes exportables
- Router de dashboards Coach y Cliente
- WhatsApp presencial desde UI
- Enlaces institucionales / cupones

Trazabilidad de negocio y priorización: [`ANALISIS_DOCUMENTAL_STAYFIT_V4.md`](ANALISIS_DOCUMENTAL_STAYFIT_V4.md).
