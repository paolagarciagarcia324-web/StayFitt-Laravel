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

## Módulos del panel (alineados a doc v4.0)

| Módulo | Ruta | Capacidad |
|--------|------|-----------|
| Dashboard | `/admin` | KPIs y métricas |
| Solicitudes | `/admin/solicitudes` | Validar/rechazar comprobantes (RF-029) |
| Pagos | `/admin/pagos` | Listado + detalle + actualizar estado |
| Usuarios | `/admin/usuarios` | Alta presencial + editar + reset password (sin rol ADMIN) |
| Roles | `/admin/roles` | Catálogo de roles (lectura) |
| Configuración | `/admin/configuracion` | Parámetros `configuracion_sistema` |
| Clientes virtuales/presenciales | `/admin/clientes/*` | Listados + detalle, asignar coach, accesos (RF-002, RF-009, RF-031) |
| Instituciones | `/admin/instituciones` | CRUD, vincular cliente, enlaces token (RF-054/055) |
| Coaches | `/admin/coaches` | Crear (password temporal) + editar + desactivar |
| Planes | `/admin/planes` | Catálogo + cupos (RF-034) |
| Cupones | `/admin/cupones` | CRUD + usos + desactivar |
| Horarios | `/admin/horarios` | Cupos sala presencial |
| Eventos | `/admin/eventos` | CRUD talleres/retos (RF-043) |
| Tienda | `/admin/tienda` | Catálogo + pedidos (RF-051) |
| WhatsApp | `/admin/whatsapp` | Config + grupos (RF-052) |
| Pasarelas | `/admin/pasarelas` | Sandbox/LIVE y activa (RF-050) |
| Calendario | `/admin/calendario` | Eventos + sesiones |
| Análisis (hub) | `/admin/analisis` | Redirect a estadísticas |
| Estadísticas | `/admin/estadisticas` | Dashboard analítico Admin/Comercial/Comunicación con filtros y gráficos (RF-049) |
| Reportes | `/admin/reportes` | Preview web + export CSV/Excel/PDF + historial (RF-047/048) |
| Comunidad | `/admin/comunidad` | Supervisión chats + moderación |

## Flujo crítico: aprobar solicitud
1. Visitante elige plan → pre-registro (`solicitudes_compra` + `pagos` en `PENDIENTE`).
2. Admin revisa solicitud y comprobante desde **Solicitudes**.
3. **Aprobar** (`SolicitudAprobacionService`): usuario + rol CLIENTE + cliente + plan_cliente + accesos + pago VALIDADO.
4. **Rechazar**: libera cupo.

## Archivos principales
- Middleware: `app/Http/Middleware/EnsureRole.php`
- Rutas: `routes/admin.php`
- Controllers: `app/Http/Controllers/Admin/*`
- Servicio: `app/Services/SolicitudAprobacionService.php`
- Análisis: `app/Services/Analitica/*` (`IndicadorService`, `SerieTemporalService`, `ReporteExportService`)
- Layout/CSS: `resources/views/layouts/admin.blade.php`, `public/css/admin.css`
- Vistas: `resources/views/admin/**`

## Pendiente (fuera de Admin o siguiente iteración)
- Webhooks reales de pasarelas / activación APPROVED automática
- Factura PDF fiscal (DomPDF/paquete dedicado)
- Reportes de progreso Coach (RF-046)
- Bitácora UI completa
- Chat en tiempo real

Trazabilidad de negocio: [`ANALISIS_DOCUMENTAL_STAYFIT_V4.md`](ANALISIS_DOCUMENTAL_STAYFIT_V4.md).
