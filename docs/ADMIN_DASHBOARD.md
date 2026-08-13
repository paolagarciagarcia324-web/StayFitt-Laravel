# Admin Dashboard — explicación y trazabilidad

## Objetivo
Panel de administración StayFit para operar el negocio sobre la BD real (`usuarios`, `planes`, `solicitudes_compra`, `pagos`, `clientes`, etc.), con la misma línea visual negro/fucsia.

## Cómo se construyó
1. Se revisaron roles (`ADMIN`, `COACH`, `CLIENTE`…) y tablas/vistas clave (`vw_dashboard_metricas`, `vw_solicitudes_pendientes`).
2. Se definió el menú Admin según dependencias del flujo: validar compras → usuarios/clientes → planes → instituciones.
3. Se añadió middleware `role:ADMIN` y redirección post-login al panel.
4. Se implementó la aprobación de solicitudes creando en BD: `usuarios` + rol `CLIENTE` + `clientes` + `planes_cliente` + `acceso_cliente_modulo` + pago `VALIDADO`.

## Módulos del panel

| Módulo | Ruta | Tablas / vistas |
|--------|------|-----------------|
| Dashboard | `/admin` | `vw_dashboard_metricas`, `vw_solicitudes_pendientes` |
| Solicitudes | `/admin/solicitudes` | `solicitudes_compra`, `pagos`, `planes` |
| Pagos | `/admin/pagos` | `pagos` |
| Planes | `/admin/planes` | `planes`, `plan_modulos`, `modulos_servicio` |
| Usuarios | `/admin/usuarios` | `usuarios`, `user_roles`, `roles` |
| Clientes | `/admin/clientes` | `clientes`, `usuarios` |
| Coaches | `/admin/coaches` | `coaches`, `usuarios` |
| Instituciones | `/admin/instituciones` | `instituciones` |

## Flujo crítico: aprobar solicitud
1. Cliente compra plan → pre-registro (`PENDIENTE`).
2. Admin revisa solicitud + comprobante.
3. **Aprobar** (`SolicitudAprobacionService`):
   - Crea/activa `usuarios` (password inicial = documento)
   - Asigna rol `CLIENTE`
   - Crea/activa `clientes`
   - Marca `pagos` como `VALIDADO`
   - Crea `planes_cliente` ACTIVO
   - Genera `acceso_cliente_modulo` según módulos del plan
   - Solicitud → `VALIDADA`
4. **Rechazar**: solicitud `RECHAZADA`, pago `RECHAZADO` (cupo queda libre).

## Archivos principales
- Middleware: `app/Http/Middleware/EnsureRole.php`
- Rutas: `routes/admin.php`
- Controllers: `app/Http/Controllers/Admin/*`
- Servicio: `app/Services/SolicitudAprobacionService.php`
- Layout/CSS: `resources/views/layouts/admin.blade.php`, `public/css/admin.css`
- Vistas: `resources/views/admin/**`

## Cómo entrar
1. Login con admin seed: `admin@figuefit.com` / `Admin123!`
2. Redirección automática a `/admin`
3. Solo rol `ADMIN` puede ver el panel (403 para otros)

## Pendiente (siguientes iteraciones)
- Alta completa de coaches desde Admin
- Devoluciones / facturas
- Reportes exportables
- Router de dashboards Coach y Cliente
