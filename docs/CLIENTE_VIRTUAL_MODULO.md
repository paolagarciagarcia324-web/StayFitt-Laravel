# Módulo Usuario Final (Cliente Virtual) — StayFit / FigueFit

Fecha: 2026-08-26  
Alcance: frontend completo del usuario final integrado con admin, módulos de plan y Design System.

## 1. Análisis de trazabilidad

| Requerimiento / CU | Módulo usuario | Función | Estado |
|---|---|---|---|
| CU-04 / CU-05 Login / logout | Auth (Breeze) | Sesión, roles, redirect `/cliente` | Implementado |
| Registro / recuperación | Auth | Register + password reset | Implementado (existente) |
| CU perfil / preferencias | Perfil | Editar datos, salud, notificaciones, password | Implementado |
| Plan activo + accesos | Mi plan | Consultar plan, módulos, coach, historial | Implementado |
| RF entrenamiento | Entrenamiento | Ver rutinas, ejercicios, completar | Implementado |
| RF nutrición | Nutrición | Ver plan macros y comidas | Implementado |
| CONTENIDO_VIRTUAL | Videos | Catálogo, reproducción, completar | Implementado |
| PROGRESO | Progreso | Listar/crear registros, metas, serie peso | Implementado |
| AGENDA | Agenda | Eventos, inscribir/cancelar | Implementado |
| COMUNICACION | Comunidad | Chat interno + invitaciones WA | Implementado (previo + shell) |
| TIENDA (presencial) | Tienda | Catálogo, carrito, checkout, órdenes | Implementado |
| Comercial planes/cupones | Comercial | Comparar planes, solicitudes, validar cupón | Implementado |
| Pagos propios | Pagos | Historial y KPIs de cobro | Implementado |
| Novedades welcome | Novedades | Feed de anuncios (no red social) | Implementado |
| Panel personal | Análisis | KPIs 30d, charts rutinas/peso | Implementado |
| Notificaciones | Notificaciones | Listar / marcar leídas | Implementado (previo) |
| Moderación contenido | Admin | Comunidad/chats/admin | Admin (fuera de cliente) |

**Decisión de producto:** no existe feed social `publicaciones/comentarios` en BD. “Comunidad” = chat (`COMUNICACION`) + novedades + WhatsApp presencial.

## 2. Arquitectura

```
BD → Modelos → Controllers Cliente → routes/cliente.php
→ layouts/cliente (DS admin) → Auth role:CLIENTE → middleware modulo:* → Tests
```

Capas respetadas: Form Requests solo donde ya existían; validación en controllers cliente; servicios `ModuloAccesoService`, `NotificacionService`, `InvitacionWhatsappService`.

## 3. Módulos creados / ampliados

- Controllers: `Dashboard`, `Perfil`, `Plan`, `Pago`, `Comercial`, `Entrenamiento`, `Nutricion`, `Contenido`, `Progreso`, `Agenda`, `Analisis`, `Tienda`, `Novedad` (+ Comunicación/Notificación previos).
- Layout dinámico por `ClienteNavComposer` + `modulosActivosCliente()`.
- Seeder: `DemoClienteContenidoSeeder`.
- CSS: extensiones cliente en `public/css/admin-ds.css`.

## 4. Base de datos utilizada

`clientes`, `preferencias_cliente`, `planes_cliente`, `acceso_cliente_modulo`, `modulos_servicio`, `planes_entrenamiento`, `rutinas`, `rutina_ejercicios`, `ejercicios`, `registro_rutinas`, `planes_nutricionales`, `comidas`, `videos`, `categorias_video`, `progreso_videos`, `registros_progreso`, `metas_cliente`, `eventos`, `evento_participantes`, `pagos`, `planes`, `solicitudes_compra`, `descuentos_cupones`, `productos_tienda`, `carritos`, `carrito_items`, `ordenes_tienda`, `orden_detalle`, `novedades_welcome`, `chats`, `mensajes`, `notificaciones`, `invitaciones_whatsapp`.

## 5. Vistas

Dashboard, perfil (virtual) / **ficha presencial** (`/cliente/presencial`), mi plan, pagos, comercial (planes/detalle/solicitudes/cupones), entrenamiento (+rutina), nutrición (+detalle), contenido (+show), progreso (+create), agenda, análisis, tienda (index/carrito/órdenes), novedades, comunicación, notificaciones.

## 6. Seguridad

- Middleware `auth` + `role:CLIENTE`.
- Gates `modulo:*` por código de plan.
- Fix: `estado_cliente` `ACTIVO`/`ACTIVA` ambos operativos.
- Ownership: rutinas/planes nutricionales/progreso/carrito solo del cliente autenticado.
- WhatsApp accept/close fuera del gate de chat (presencial).

## 7. Pruebas

`tests/Feature/Cliente/ClienteVirtualTest.php`: acceso, perfil, 403 sin módulo, completar rutina, progreso, aislamiento de rol.

Ejecutar (PHP ≥ 8.3):

```bash
# Crear BD de pruebas una vez: stayfit_test (usa credenciales de .env)
php artisan test --filter=ClienteVirtual
php artisan db:seed --class=DemoClienteContenidoSeeder
```

Nota: `phpunit.xml` usa MySQL `stayfit_test` (SQLite no soporta bien las vistas analíticas del proyecto).

Demo: `valen.gomez@demo.figuefit.test` / `demo12345`.

## 8. Errores corregidos en esta entrega

1. Acceso bloqueado con `estado_cliente = ACTIVO` (solo aceptaba `ACTIVA`).
2. Dashboard cliente incompleto (solo chat/notif).
3. Menú estático sin módulos del plan.
4. Layout sin Design System (`admin-ds.css`).
5. Sin seed de contenido consumo (rutinas/videos/comidas).

## 9. Estado final

**Módulo usuario final: COMPLETO para presentación**, integrado con admin (pagos/solicitudes/tienda/comunidad/WA), con trazabilidad documentada y pruebas feature base.

Pendientes opcionales (no bloquean demo): foto de perfil upload, pasarela de renovación in-app, checkout cupón en línea de pago, PWA.
