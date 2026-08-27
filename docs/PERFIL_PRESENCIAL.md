# Perfil Usuario Presencial — `perfilpresencial01`

Módulo de **expediente / ficha presencial** del cliente FigueFit. No es un CRUD: es un hub member premium con hero, KPIs, tabs y acciones operativas (check-in, progreso, membresía, pagos, WA).

## 1. Trazabilidad

| Requisito / CU | Funcionalidad | Módulo | Tablas | Vista |
|---|---|---|---|---|
| RF-031 / CU-35 acceso módulos | Ver módulos activos del plan | Ficha → Resumen | `acceso_cliente_modulo`, `modulos_servicio` | Tab Resumen |
| CU-53 medidas iniciales | Baseline peso/estatura/objetivo | Ficha → Personal / Entrenamiento | `clientes` | Tabs Personal, Entrenamiento |
| CU-52 WhatsApp presencial | Estado invitaciones WA | Ficha → Resumen | `invitaciones_whatsapp` | Card Comunicación |
| CU-51 tienda presencial | Acceso rápido tienda sala | Ficha → Resumen | `productos_tienda` (vía ruta) | CTA Tienda |
| Membresía / renovación | Plan activo + historial | Ficha → Membresía | `planes_cliente`, `planes` | Tab Membresía |
| Pagos | Historial y próximo pago | Ficha → Pagos | `pagos` | Tab Pagos |
| Asistencia sala | Check-in + calendario + racha | Ficha → Asistencia | `asistencias`, `rachas_cliente` | Tab Asistencia |
| Progreso físico | Serie peso, metas, medidas | Ficha → Progreso | `registros_progreso`, `metas_cliente` | Tab Progreso |
| Entrenamiento | Plan coach + rutinas | Ficha → Entrenamiento | `planes_entrenamiento`, `rutinas`, `registro_rutinas` | Tab Entrenamiento |
| Perfil / preferencias | Edición datos + notifs WA | Ficha → Personal | `users`, `clientes`, `preferencias_cliente` | Tab Personal |
| Admin clientes presenciales | Misma ficha de datos | Admin (existente) | mismas tablas | `/admin/clientes/presenciales` |

**Fuera de alcance (no justificado como CRUD nuevo):** feed social, pasarela de renovación in-app, upload de foto (se usa `foto_perfil_url` existente).

## 2. Arquitectura

```
BD (clientes, asistencias, planes_cliente, pagos, …)
  → Models
  → PerfilPresencialService (agrega expediente + check-in + racha)
  → PerfilPresencialController
  → routes/cliente.php (`cliente.presencial.*`)
  → views/cliente/presencial/perfil.blade.php
  → auth role:CLIENTE + gate modalidad=PRESENCIAL
  → tests/Feature/Cliente/PerfilPresencialTest.php
```

## 3. Rutas

| Método | URI | Nombre | Acción |
|---|---|---|---|
| GET | `/cliente/presencial` | `cliente.presencial.perfil` | Hub ficha (`?tab=`) |
| POST | `/cliente/presencial/asistencia` | `cliente.presencial.checkin` | Check-in día |
| PUT | `/cliente/presencial` | `cliente.presencial.update` | Actualizar ficha |
| PUT | `/cliente/presencial/password` | `cliente.presencial.password` | Password |

`GET /cliente/perfil` redirige a la ficha si `modalidad = PRESENCIAL`.

## 4. UI

- Design System `cv-*` (`public/css/client-ds.css`)
- Hero con avatar XL, estado, plan, CTAs
- KPIs: membresía, próximo pago, asistencias mes, progreso/racha
- Tabs: Resumen · Personal · Membresía · Entrenamiento · Progreso · Asistencia · Pagos
- Calendario mensual de asistencias + gráficos de barras CSS

## 5. Integraciones

- **Admin:** mismos datos de cliente / planes / pagos
- **Comercial:** CTAs a planes y renovación
- **Comunicación:** invitaciones WA + notificaciones + chat/comunidad
- **Análisis:** enlace desde dashboard; métricas de asistencia alimentan racha
- **Nav member:** ítem “Ficha presencial” solo si modalidad PRESENCIAL

## 6. Demo

Tras `php artisan demo:data --force`, clientes PRESENCIAL (p. ej. `julian.rojas@demo.figuefit.test` / `demo12345`) tienen asistencias, racha y meta demo.

## 7. Pruebas

```bash
C:\php83\php.exe artisan test --filter=PerfilPresencial
```

Cubre: vista hub, tabs, redirect virtual↔presencial, check-in idempotente, update ficha.

## 8. Estado

**Listo para demo** del expediente presencial member. Pendientes opcionales: upload foto, QR check-in en recepción, vista coach del mismo expediente.
