# StayFit v4.0 — Análisis documental, trazabilidad y negocio

**Versión:** 4.0 · FigueFit  
**Fuente:** carpeta `StayFitDocumentación/` + `docs/` de implementación  
**Propósito:** inventario archivo por archivo, mapa de negocio, cadena RF→HU→CU→BD y decisión de siguiente entrega.

---

## 1. Inventario leído (sin omitir)

### 1.1 Análisis — `StayFitDocumentación/Analisis/Correctos/`

| Archivo | Rol |
|---------|-----|
| `DOCUMENTO_INTEGRAL_STAYFIT_v4.docx` | Fuente maestra de negocio: actores, 3 caminos de compra, módulos 1–12, innovaciones, reglas críticas |
| `StayFit_SRS.docx` / `PDF/StayFit_SRS.pdf` | RF-001…055, RNF-01…12, actores, interfaces, glosario |
| `StayFit_Propuesta_Tecnica.docx` / `PDF/StayFit_Propuesta_Tecnica.pdf` | Problema, objetivos, arquitectura, flujos 7.1–7.4, integraciones, riesgos |
| `StayFit_Casos_de_Uso.docx` / `PDF/StayFit_Casos_de_Uso.pdf` | CU-01…55 + 10 reglas transversales v4.0 |
| `StayFit_Historias_de_Usuario.xlsx` | Hojas: Resumen, Requisitos_Funcionales, Historias_Usuario, Criterios_Aceptacion, Matriz_Trazabilidad, Roles_Modulos |
| `stayfittt.sqlite` | Vacío (0 KB) — **no usar** como referencia |

### 1.2 Base de datos — `StayFitDocumentación/BD/`

| Archivo | Rol |
|---------|-----|
| `stayfit.sql` | Esquema MySQL canónico (~77 tablas + 7 vistas + `matriz_trazabilidad`) |
| `stayfittt_explicacion_tablas.md` | Glosario funcional tabla por tabla + mapa proceso→tablas |
| `stayfittt.sqlite` | Dump SQLite de referencia del esquema (~544 KB) |

### 1.3 Implementación Laravel — `docs/`

| Archivo | Rol |
|---------|-----|
| `GUIA_ORDEN_DESARROLLO_STAYFITT.md` | Fases 0–12 y estado real de construcción |
| `ADMIN_DASHBOARD.md` | Panel Admin + flujo aprobar solicitud |
| `LOGIN.md` | Auth contra `usuarios.correo` / `password_hash` |
| `ANALISIS_DOCUMENTAL_STAYFIT_V4.md` | Este documento (síntesis operativa) |

---

## 2. Idea de negocio

**StayFit** es el ecosistema digital de **FigueFit** para vender y operar planes fitness **Virtual / Presencial / Mixto / Institucional**, con acceso estricto por **plan + pago + módulo + vigencia**.

Diferenciación:

| Modalidad | Comunicación | Contenido destacado | Tienda |
|-----------|--------------|---------------------|--------|
| Virtual | Chat interno | Videos + IA postura/nutrición | No |
| Presencial | WhatsApp | Asistencia + rachas | Sí (exclusiva) |
| Institucional | Como Virtual | Enlace token / cupos convenio | No (salvo reglas del plan) |

Organización: FigueFit (Diana Paola Figueroa Polanía). Documentación v4.0: Yina Paola García García.

---

## 3. Actores (7)

1. **Visitante** — Welcome, compra Virtual/Presencial, CTA “Crear Cuenta Presencial”
2. **Cliente Individual Virtual** — módulos digitales del plan
3. **Cliente Individual Presencial** — dashboard presencial, WhatsApp, tienda
4. **Cliente Institucional Virtual** — enlace token; trazabilidad individual
5. **Coach** — entrenamiento, nutrición, progreso, agenda, chat o WhatsApp
6. **Administrador** — catálogo, usuarios, pagos, instituciones, tienda, config
7. **Sistema** — webhooks, cupos, vencimientos, notificaciones, rachas, auditoría

---

## 4. Flujos comerciales (trazabilidad de negocio)

```
Visitante → Welcome → Solicitud
  ├─ Camino A (pasarela)     → Webhook firmado APPROVED → Activación
  ├─ Camino B (comprobante)  → Admin valida/rechaza     → Activación o libera cupo
  └─ Camino C (presencial)   → Medidas + pago           → Activación + alert WhatsApp

Activación → usuarios + rol → clientes → planes_cliente → acceso_cliente_modulo (+ factura)
         → Dashboard Virtual | Dashboard Presencial
```

**Corazón de tablas:**  
`planes` → `solicitudes_compra` → `pagos` / `transacciones_pasarela` → `planes_cliente` → `acceso_cliente_modulo` (+ `facturas`, `intentos_activacion`).

---

## 5. Trazabilidad canónica RF ↔ HU ↔ CU ↔ BD

**Cadena única v4.0:** `RF-00n` ↔ `HU-ACC-00n` ↔ `CU-0n` ↔ tablas ↔ módulo ↔ diagramas DA/DS

| Bloque | IDs | Módulo documental |
|--------|-----|-------------------|
| Acceso / usuarios | 001–006 | Usuarios y acceso |
| Perfil / coach | 007–009, 053 | Perfil y clientes |
| Entrenamiento | 010–016 | Entrenamiento |
| Nutrición + IA | 017–021 | Nutrición |
| Progreso | 022–027 | Progreso |
| Pagos / cupos / accesos | 028–035, 050 | Pagos y acceso |
| Comunicación | 036–039, 052 | Comunicación |
| Agenda / eventos | 040–045 | Agenda y sesiones |
| Reportes | 046–049 | Reportes y analítica |
| Tienda | 051 | Tienda E-commerce |
| Instituciones | 054–055 | Instituciones |

### 5.1 Casos de uso (CU-01…55)

| CU | Título |
|----|--------|
| CU-01 | Inicio de compra y registro de cliente |
| CU-02 | Registro administrativo de clientes |
| CU-03 | Registro de coaches |
| CU-04 | Inicio de sesión |
| CU-05 | Recuperación de contraseña |
| CU-06 | Gestión de usuarios |
| CU-07 | Actualización de datos personales |
| CU-08 | Actualización de datos físicos y de salud |
| CU-09 | Asignación de coach |
| CU-10 | Creación de plan de entrenamiento |
| CU-11 | Asignación de rutinas |
| CU-12 | Carga de material de entrenamiento |
| CU-13 | Visualización de rutinas |
| CU-14 | Registro de rutinas completadas |
| CU-15 | Modificación de rutinas |
| CU-16 | Programación de entrenamientos |
| CU-17 | Creación de plan nutricional |
| CU-18 | Asignación de plan nutricional |
| CU-19 | Visualización de plan nutricional |
| CU-20 | Actualización de plan nutricional |
| CU-21 | Análisis nutricional con IA |
| CU-22 | Registro de peso |
| CU-23 | Registro de medidas corporales |
| CU-24 | Registro fotográfico de progreso |
| CU-25 | Historial de progreso |
| CU-26 | Comparación de progreso |
| CU-27 | Observaciones del coach sobre progreso |
| CU-28 | Gestión de pagos por pasarela o comprobante |
| CU-29 | Validación de pagos manuales |
| CU-30 | Activación automática por pago confirmado |
| CU-31 | Restricción de acceso a módulos |
| CU-32 | Historial de pagos y facturas |
| CU-33 | Vencimiento de planes |
| CU-34 | Gestión de catálogo de planes comerciales |
| CU-35 | Consulta de estado de acceso a módulos |
| CU-36 | Comunicación virtual por chat interno |
| CU-37 | Notificaciones y comunicación grupal virtual |
| CU-38 | Mensajes motivacionales automáticos |
| CU-39 | Notificaciones del sistema |
| CU-40 | Agenda de entrenamientos del coach |
| CU-41 | Calendario de actividades del cliente |
| CU-42 | Recordatorios automáticos |
| CU-43 | Gestión de eventos y talleres |
| CU-44 | Gestión de participantes en sesiones y eventos |
| CU-45 | Gestión de disponibilidad del coach |
| CU-46 | Reportes de progreso del coach |
| CU-47 | Reportes administrativos |
| CU-48 | Reportes de pagos |
| CU-49 | Estadísticas del sistema |
| CU-50 | Integración con pasarelas de pago colombianas |
| CU-51 | Tienda e-commerce para clientes presenciales |
| CU-52 | Comunicación presencial vía WhatsApp |
| CU-53 | Medidas antropométricas iniciales en registro presencial |
| CU-54 | Gestión de instituciones |
| CU-55 | Vinculación de clientes a instituciones |

Detalle de criterios, actores y tablas: Excel `Matriz_Trazabilidad` y tabla BD `matriz_trazabilidad`.

---

## 6. Reglas de negocio que no se pueden romper

1. Formulario ≠ cliente activo.
2. Compra solo con plan `ACTIVO` y cupo no `CUPO_LLENO` (`vw_cupos_planes`).
3. `PENDIENTE` / `VALIDADA` reservan cupo; `RECHAZADA` / `CANCELADA` liberan.
4. Pasarela: webhook firmado + `APPROVED` → activación automática e **idempotente**.
5. Comprobante: solo Admin valida (`SolicitudAprobacionService`).
6. Módulos visibles solo con `acceso_cliente_modulo` activo y vigente.
7. Virtual → chat interno; Presencial → WhatsApp; tienda **solo** presencial.
8. Medidas del alta presencial = línea base (`medidas_antropometricas_iniciales`).
9. Institución no sustituye la identidad del cliente.
10. Operaciones críticas auditables (`bitacora_sistema`, historiales).

---

## 7. Persistencia (lectura de BD)

Orden lógico del esquema (`stayfit.sql`):

1. Seguridad: `roles`, `user`/`usuarios`, `user_roles`, `tokens_recuperacion`, `sesiones_auth`
2. Personas: `instituciones`, `coaches`, `clientes`, preferencias, historiales, valoraciones, medidas iniciales
3. Catálogo: `planes`, `modulos_servicio`, `plan_modulos`, cupones, enlaces institucionales
4. Comercial: solicitudes, pagos, facturas, suscripciones, accesos, pasarelas, webhooks, activación
5. Virtual: programas, videos, progreso, validaciones IA
6. Entrenamiento / nutrición / progreso / agenda
7. Chat + WhatsApp + notificaciones
8. Tienda presencial
9. Reportes, bitácora, `configuracion_sistema`, `matriz_trazabilidad`

**Vistas clave:** `vw_cupos_planes`, `vw_clientes_activos`, `vw_pagos_por_estado`, `vw_accesos_vigentes`, `vw_progreso_resumen`, `vw_ingresos_pasarela`, `vw_asistencia_racha`.

**Nota Laravel:** el SQL documental usa tabla `user`; la app usa `usuarios` (migraciones `2026_08_16_*`). Misma intención, distinto nombre físico.

Glosario tabla a tabla: [`StayFitDocumentación/BD/stayfittt_explicacion_tablas.md`](../StayFitDocumentación/BD/stayfittt_explicacion_tablas.md).

---

## 8. Estado frente al código (ago 2026)

| Área documental | Estado en Laravel |
|-----------------|-------------------|
| Auth Admin + Welcome + planes | Hecho |
| Compra / comprobante / activación manual | Hecho (MVP) |
| Catálogo Admin (clientes, coaches, instituciones, planes) | MVP reciente |
| Router Coach / Cliente + menú por módulos | Pendiente |
| Pasarelas reales + webhooks | Pendiente (hay simulación/config) |
| WhatsApp presencial + alert post-registro | Pendiente |
| Coach asigna contenido + Cliente consume | Pendiente |
| IA nutrición / validación video | Pendiente |
| Reportes / bitácora / facturas PDF | Pendiente |

Referencia de fases: [`GUIA_ORDEN_DESARROLLO_STAYFITT.md`](GUIA_ORDEN_DESARROLLO_STAYFITT.md).

---

## 9. Decisión de siguiente entrega

**Elección por defecto (alineada a la guía de desarrollo):**  
no reabrir el catálogo Admin como prioridad; avanzar la **siguiente milla MVP**:

1. **Router por rol** — shells `/coach`, `/cliente/virtual`, `/cliente/presencial` (Fase 3)
2. **Menú por `acceso_cliente_modulo`** + perfil (Fase 6)
3. **Punta a punta mínima** — Coach asigna 1 rutina → Cliente la completa (Fases 7/9)

**Alternativas disponibles si Jean las pide explícitamente:**

| Opción | Cuándo |
|--------|--------|
| Auditoría gaps docs vs código (matriz RF→ruta→estado) | Antes de estimar cierre académico |
| Roadmap MVP detallado por sprint | Planificación de entregas |
| Pulido/ajuste Admin (cupones, enlaces institucionales, facturas) | Si el foco sigue siendo operación Admin |

---

## 10. Cómo usar este documento

Cualquier feature nueva debe anclarse a:

1. **RF + CU** del bloque correspondiente  
2. **Tablas** del glosario BD  
3. **Regla de modalidad** (Virtual vs Presencial)  
4. **Fase** de [`GUIA_ORDEN_DESARROLLO_STAYFITT.md`](GUIA_ORDEN_DESARROLLO_STAYFITT.md)

| Fuente | Usar para |
|--------|-----------|
| Documento Integral + Excel Matriz | Verdad de negocio y trazabilidad |
| `stayfit.sql` + explicación de tablas | Persistencia |
| Guía de orden de desarrollo | Orden de construcción en Laravel |
