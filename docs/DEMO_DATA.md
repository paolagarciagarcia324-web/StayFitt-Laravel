# Datos demo profesionales — StayFit / FigueFit

**Bundle ID:** `demodata01`  
**Comando:** `php artisan demo:data`  
**Forzar regeneración:** `php artisan demo:data --force`

## Cómo cargar

```bash
C:\php83\php.exe artisan demo:data
# o
C:\php83\php.exe artisan db:seed
```

## Credenciales

| Rol | Correo | Contraseña | Entrada |
|---|---|---|---|
| Administrador | `admin@figuefit.com` | `Admin123!` | `/admin` |
| Admin existente | `paola@correo.com` | (la tuya) | `/admin` |
| Cliente virtual | `valen.gomez@demo.figuefit.test` | `demo12345` | `/cliente` |
| Cliente virtual (manual) | `cliente.virtual@figuefit.test` | `Virtual123!` | `/cliente` |
| Coach | `laura.coach@demo.figuefit.test` | `demo12345` | `/coach` |
| Asistente | `asistente@demo.figuefit.test` | `demo12345` | `/staff` |
| Responsable institucional | `rrhh@campoalegre.demo.figuefit.test` | `demo12345` | `/institucional` |
| Cliente institucional (empleado) | `diana.suarez@demo.figuefit.test` | `demo12345` | `/cliente` |

Todos los correos `@demo.figuefit.test` usan password **`demo12345`**.

## Qué se crea (trazabilidad real del esquema)

| Entidad | Cantidad aprox. | Notas |
|---|---|---|
| Usuarios | 27–30 | Admin, coaches, 22 clientes, asistente |
| Clientes | 22+ | Virtual / presencial / institucional |
| Coaches | 4 | Activos |
| Planes | 10 | Catálogo base + extras demo |
| Productos tienda | 30+ | Suplementos, ropa, equipo, hidratación |
| Videos | 15+ (programa) + intros propuestas | Publicados |
| Propuestas coach | 15 | Borrador / revisión / aprobado / publicado / rechazado |
| Cupones | 10 | FIGUEFIT10, PREMIUM20, BIENVENIDA15… |
| Solicitudes | 22 | Varios estados |
| Pagos | 45+ | Pendientes / validados / rechazados + serie temporal |
| Órdenes tienda | 20 | Con detalle |
| Chats / mensajes | 13 / 120+ | Comunidad = chat (no hay tabla publicaciones) |
| Notificaciones | 50+ | Clientes + admin |
| Eventos | 6 | Con inscritos |
| Entrenamiento / nutrición / progreso | Sí | Para paneles cliente y análisis |

## Decisiones de producto

- **Comunidad:** no existen tablas `publicaciones` / `comentarios`. El seeder llena **chats + mensajes + novedades**.
- **Propuestas comerciales del brief:** se modelan como `propuestas_plan` (flujo coach→admin) + `solicitudes_compra` (cliente→admin).
- Idempotente: marca `configuracion_sistema.demo_data_id = demodata01`.

## Archivos

- `database/seeders/DemoDataSeeder.php`
- `app/Console/Commands/SeedDemoDataCommand.php`
- `database/seeders/DatabaseSeeder.php` → llama `DemoDataSeeder`
