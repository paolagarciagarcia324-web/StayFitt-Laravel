# StayFit v4.0 — Guía de orden de desarrollo (Laravel)

> Orden de implementación alineado al flujo integral StayFit / FigueFit.
>
> **Estado actual del proyecto (ago 2026)**
> - BD MySQL **`stayfit`** con esquema v4.0 (`2026_08_16_*`)
> - Auth real contra `usuarios` + `roles` + `user_roles`
> - Welcome con planes activos, login FigueFit, Admin Dashboard operativo
> - Pre-registro / solicitudes / validación de pagos (flujo manual)
> - **Pendiente:** dashboards Coach/Cliente, módulos de consumo, pasarelas, WhatsApp, tienda
>
> **Stack:** Laravel · MySQL 8 · Blade (Breeze adaptado) · BD `stayfit`

---

## Respuesta corta: ¿por dónde voy ahora?

| Ya hecho | Siguiente prioridad |
|----------|---------------------|
| Auth + roles + Admin shell | Dashboard Coach / Cliente (router completo) |
| Planes + módulos + seeds | Menú cliente según `acceso_cliente_modulo` |
| Pre-registro + aprobar solicitud | Contenido Coach (rutinas, nutrición) |
| Vistas de métricas | Pasarelas / WhatsApp / tienda presencial |

**Orden coherente:**

```
Auth + Welcome + Admin  →  Router Coach/Cliente  →  Menú por módulos
  →  Coach asigna contenido  →  Cliente consume (Virtual / Presencial)
  →  Chat / notificaciones  →  Pasarelas + WhatsApp + tienda
```

---

## Cómo usar esta guía

1. Completa una **fase** antes de saltar a hojas del árbol (rutinas, tienda, IA).
2. Cada fase indica tablas, entregables y criterio de listo.
3. Marca el checklist al terminar.

### Leyenda diagrama → Laravel

| Tipo en diagrama | Implementación |
|------------------|----------------|
| Pantalla | Ruta + Controller + Vista Blade |
| Decisión | Middleware / Policy / redirect |
| Proceso BD | Service + Eloquent + Form Request |
| Error | Validación + flash |
| Servicio externo | Job + API (pasarela, WhatsApp, IA) |
| BD | Modelos alineados a migraciones `2026_08_16_*` |

---

## FASE 0 — Fundación técnica

### Objetivo
Conectar Laravel al esquema StayFit (`usuarios`, roles, sesión).

### Tablas
`usuarios`, `roles`, `user_roles`, `tokens_recuperacion`, `clientes`, `coaches`

### Estado
- [x] Modelo `User` → tabla `usuarios`, PK `id_user`, `password_hash`
- [x] `tieneRol()` vía `user_roles`
- [x] Login contra `correo`
- [x] Middleware `EnsureRole` / `role:ADMIN`
- [x] Seed admin (`paola@correo.com`)
- [x] Migraciones v4.0 + BD `stayfit`

### Criterio de listo
Puedes autenticarte y ver roles. **Cumplido.**

---

## FASE 1 — Welcome / Landing

### Objetivo
Página pública: marca, planes y CTA a login / pre-registro.

### Tablas
`planes`, `plan_modulos`, `modulos_servicio`

### Estado
- [x] `/` Landing FigueFit (no welcome Laravel por defecto)
- [x] Listado de planes `ACTIVO` desde BD
- [x] CTA login y flujo de compra/pre-registro

### Criterio de listo
Visitante entiende el producto y puede iniciar compra o sesión. **Cumplido en MVP.**

---

## FASE 2 — Auth (Login / recuperación)

### Objetivo
Cerrar “¿Usuario registrado?”.

### Notas v4.0
- **No hay registro público de cliente**: el alta va por compra/pre-registro o Admin.
- Formulario `email` → columna `correo`; columna `email` es alias Breeze.
- Contraseñas nuevas en bcrypt; texto plano legacy se migra al primer login.

### Estado
- [x] Login + Logout
- [x] Redirect Admin → `/admin`
- [ ] Recuperación de contraseña 100 % alineada a `tokens_recuperacion` (Breeze usa `password_reset_tokens`)
- [ ] Verificación de correo operativa en producción

### Criterio de listo
Login real contra BD. **Cumplido para acceso Admin.**

Detalle: [`docs/LOGIN.md`](LOGIN.md)

---

## FASE 3 — Router por rol

### Objetivo
Tras login, enrutar Admin / Coach / Cliente (Virtual | Presencial).

### Estado
- [x] Admin → `/admin`
- [x] Dispatcher básico en `/dashboard`
- [ ] Shell `/coach`
- [ ] Shell `/cliente/virtual` y `/cliente/presencial`
- [ ] Middleware de modalidad

### Criterio de listo
Diamante de roles completo. **Parcial (solo Admin cerrado).**

---

## FASE 4 — Catálogo Admin (base de negocio)

### Orden interno
1. Instituciones  
2. Planes + `plan_modulos`  
3. Coaches  
4. Usuarios / roles  
5. Cupones / enlaces institucionales  

### Tablas v4.0
`instituciones`, `planes`, `modulos_servicio`, `plan_modulos`, `usuarios`, `user_roles`, `coaches`, `descuentos_cupones`, `enlaces_registro_institucional`  
*(La agenda presencial usa `disponibilidades` / `sesiones`, no la antigua `horarios`.)*

### Estado
- [x] CRUD planes + módulos
- [x] Listados usuarios, clientes, coaches, instituciones
- [x] Seeds: 3 planes ejemplo + módulos + pasarelas config
- [ ] Alta completa de coach (usuario + perfil) desde UI rica
- [ ] CRUD cupones / enlaces institucionales

Detalle: [`docs/ADMIN_DASHBOARD.md`](ADMIN_DASHBOARD.md)

---

## FASE 5 — Flujo comercial: solicitud → pago → activación

```
Ver plan → Pre-registro → Pago (comprobante) → Validación admin
  → planes_cliente → acceso_cliente_modulo → Cliente usa módulos
```

### Tablas
`solicitudes_compra`, `pagos`, `facturas`, `planes_cliente`, `acceso_cliente_modulo`, `transacciones_pasarela`, `suscripciones_recurrentes`

### Estado
- [x] Pre-registro + comprobante
- [x] Admin aprueba/rechaza (`SolicitudAprobacionService`)
- [x] Activación de cliente + plan + accesos
- [ ] Factura PDF automática
- [ ] Pasarelas (Wompi, PayU, MercadoPago, Nequi, Daviplata) + webhooks
- [ ] Renovaciones / suscripciones

### Criterio de listo (manual)
Cliente de prueba “compra” y queda con módulos. **Flujo manual cumplido; pasarelas pendientes.**

---

## FASE 6 — Menú Cliente según acceso

### Tablas
`acceso_cliente_modulo`, `modulos_servicio`, `planes_cliente`

### Códigos de módulo
`PERFIL`, `ENTRENAMIENTO`, `NUTRICION`, `CONTENIDO_VIRTUAL`, `PROGRESO`, `AGENDA`, `PAGOS`, `COMUNICACION`, `TIENDA`, `SESIONES_GRUPALES`

### Estado
- [ ] Menú dinámico por acceso vigente
- [ ] Página “Sin acceso”
- [ ] Perfil cliente (`usuarios` + `clientes` + medidas)

---

## FASE 7 — Cliente Virtual

### 7.1 Entrenamiento
`planes_entrenamiento`, `rutinas`, `ejercicios`, `rutina_ejercicios`, `registro_rutinas`, `logs_ejercicio_detalle`, `material_entrenamiento`

### 7.2 Videos + rachas
`programas_virtuales`, `categorias_video`, `videos`, `progreso_videos`, `validaciones_ia_video`, `rachas_cliente`

### 7.3 Nutrición + IA
`planes_nutricionales`, `comidas`, `consultas_ia_nutricion`

### 7.4 Progreso
`registros_progreso`, `fotos_progreso`, `metas_cliente`, `medidas_antropometricas_iniciales`

### 7.5 Agenda
`recordatorios`, `eventos`, `evento_participantes`

### Estado
- [ ] Pendiente de consumo UI (tablas ya en BD)

---

## FASE 8 — Cliente Presencial

### Agenda / asistencia
`disponibilidades`, `sesiones`, `sesion_participantes`, `asistencias`, `rachas_cliente`

### Comunicación v4.0
`configuracion_whatsapp`, `grupos_whatsapp`, `invitaciones_whatsapp`  
*(Chat interno es para Virtual; Presencial usa WhatsApp.)*

### Tienda v4.0 (solo presencial)
`productos_tienda`, `producto_variantes`, `carritos`, `ordenes_tienda`, `orden_detalle`

### Estado
- [ ] Pendiente UI; esquema BD listo

---

## FASE 9 — Dashboard Coach

1. Clientes asignados (`clientes.id_coach` / `id_coach_asignado`)  
2. Rutinas y planes de entrenamiento  
3. Nutrición  
4. Progreso / asistencia  
5. Disponibilidades y sesiones  
6. Chat (virtual) / links WhatsApp (presencial)

### Estado
- [ ] Shell y CRUDs Coach pendientes

---

## FASE 10 — Admin avanzado

- [ ] Facturas / devoluciones  
- [ ] Reportes (`reportes_generados` + `vw_*`)  
- [ ] Bitácora (`bitacora_sistema`)  
- [ ] Renovaciones  
- [ ] Enlaces institucionales  
- [ ] Config pasarelas / WhatsApp desde UI  

---

## FASE 11 — Transversales

| Módulo | Tablas | Estado |
|--------|--------|--------|
| Notificaciones | `notificaciones` | Pendiente UI |
| Chat virtual | `chats`, `chat_participantes`, `mensajes` | Pendiente UI |
| Tienda presencial | `productos_tienda`, `ordenes_tienda`, … | Pendiente UI |
| Encuestas | `encuestas_satisfaccion`, `valoraciones_coach` | Pendiente UI |

---

## FASE 12 — Integraciones y endurecimiento

1. Pasarelas + webhooks firmados + idempotencia (`transacciones_pasarela`, `eventos_webhook`)  
2. WhatsApp Business  
3. Emails / colas  
4. Storage (comprobantes, fotos, videos)  
5. IA nutrición y validación de video  
6. Tests Pest de auth, pago y accesos  
7. Cliente institucional  

---

## Mapa rápido: diagrama → fase

| Bloque | Fase | Estado |
|--------|------|--------|
| Landing / planes públicos | 1 | Hecho |
| Login | 0–2 | Hecho |
| Panel Admin base | 4 + 5 | Hecho (MVP) |
| Router Coach/Cliente | 3 | Parcial |
| Compra / activación | 5 | Manual hecho |
| Cliente Virtual / Presencial | 6–8 | Pendiente |
| Coach | 9 | Pendiente |
| Chat / tienda / WhatsApp | 11–12 | Pendiente |

---

## Roadmap MVP restante

1. Router + shells Coach y Cliente  
2. Menú por `acceso_cliente_modulo` + perfil  
3. Coach asigna 1 rutina → cliente la completa  
4. Pasarela 1 (p. ej. Wompi) + activación por webhook  
5. WhatsApp invitación post-registro presencial  
6. Tienda presencial mínima  

---

## Convención de carpetas

```
app/
  Models/
  Http/Controllers/{Auth,Admin,Coach,Cliente}/
  Http/Middleware/          # EnsureRole, EnsureModuloAcceso, ...
  Services/                 # SolicitudAprobacionService, PagoService, ...
database/migrations/
  2026_08_16_*.php          # Esquema stayfit v4.0 (activo)
  2026_03_01_*.php          # Legacy deshabilitado (no-op)
resources/views/{admin,auth,pre-registro,...}
docs/
  LOGIN.md
  ADMIN_DASHBOARD.md
  GUIA_ORDEN_DESARROLLO_STAYFITT.md
  ANALISIS_DOCUMENTAL_STAYFIT_V4.md   # inventario docs + trazabilidad RF/HU/CU/BD
```

---

## Reglas de coherencia

1. No construyas módulos de cliente antes del router de roles.  
2. No desbloquees entrenamiento/nutrición/tienda sin `acceso_cliente_modulo` vigente.  
3. **Admin crea catálogo → Coach asigna → Cliente consume.**  
4. Virtual = chat interno + videos; Presencial = WhatsApp + tienda.  
5. Pasarelas después del flujo manual interno.  
6. Login siempre contra `usuarios.correo`, no contra `users` de Breeze.

---

## Checklist maestro

- [x] Fase 0 — Fundación Auth + modelos  
- [x] Fase 1 — Welcome / Landing  
- [x] Fase 2 — Login operativo (Admin)  
- [ ] Fase 3 — Router Coach/Cliente completo  
- [x] Fase 4 — Catálogo Admin MVP  
- [x] Fase 5 — Compra / pago manual / activación  
- [ ] Fase 6 — Menú módulos + perfil  
- [ ] Fase 7 — Cliente Virtual  
- [ ] Fase 8 — Cliente Presencial  
- [ ] Fase 9 — Coach  
- [ ] Fase 10 — Admin avanzado  
- [ ] Fase 11 — Chat / notificaciones / tienda  
- [ ] Fase 12 — Integraciones  

---

## Conclusión operativa

La **primera milla** (Auth + Welcome + Admin + flujo de compra manual) ya está sobre `stayfit` v4.0.  
La siguiente milla útil: **router Coach/Cliente + menú por accesos + una rutina de punta a punta**.
