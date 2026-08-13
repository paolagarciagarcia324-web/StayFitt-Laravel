# StayFitt v3.0 — Guía de orden de desarrollo (Laravel)

> Documento de referencia para desarrollar la plataforma **en el mismo orden del diagrama de flujo integral**, sin saltar dependencias.
>
> **Estado actual del proyecto**
> - Base de datos migrada (`stayfit_v3` / `stayfitt`)
> - Scaffolding Laravel Breeze (login/register/dashboard genéricos)
> - **Pendiente:** adaptar Auth al esquema real (`usuarios`, `roles`, `user_roles`, `clientes`, etc.) y construir el producto según este orden
>
> **Stack:** Laravel 13 · MySQL 8 · Blade (Breeze) · API cuando un módulo lo requiera

---

## Respuesta corta: ¿por dónde empiezo?

| Opción | ¿Es lo primero? | Por qué |
|--------|-----------------|--------|
| Welcome / Landing | **Sí, como primera pantalla del producto** | Es el inicio del diagrama (Visitante → Landing) |
| Auth / Login / Registro | **Sí, justo después (o en paralelo corto)** | El diagrama decide “¿Usuario registrado?” y sin Auth no hay roles ni dashboards |
| Dashboard Admin/Coach/Cliente | **No** | Dependen de Auth + roles + catálogo mínimo |
| Rutinas, dietas, chat, tienda | **No** | Son hojas del flujo; necesitan cliente autenticado, plan activo y datos creados por Admin/Coach |

**Orden coherente con el diagrama:**

```
Fundación Eloquent/Auth  →  Welcome  →  Auth real  →  Router por rol
      →  Shells de dashboards  →  Catálogo Admin (planes)
      →  Compra / pagos  →  Módulos Cliente (Virtual → Presencial)
      →  Módulos Coach  →  Admin avanzado  →  Transversales (chat, notificaciones, tienda, reportes)
```

**No empieces por el dashboard del cliente ni por rutinas.** El diagrama entra por Visitante → Landing → Auth → decisión de rol.

---

## Cómo usar esta guía

1. Completa una **fase** antes de pasar a la siguiente (salvo lo marcado como “puede ir en paralelo”).
2. Cada fase incluye: objetivo, tablas BD involucradas, entregables Laravel, criterio de “listo”.
3. Marca el checklist al terminar.
4. Si un módulo del diagrama no está en la BD con el mismo nombre, usa la tabla equivalente ya migrada (se indica en cada fase).

### Leyenda del diagrama → capas Laravel

| Color / tipo en el diagrama | Implementación Laravel sugerida |
|-----------------------------|----------------------------------|
| Rectángulo azul (pantalla) | Ruta + Controller + Vista Blade |
| Diamante amarillo (decisión) | Middleware / Policy / redirect en Controller |
| Rectángulo verde (proceso BD) | Service / Action + Eloquent + Form Request |
| Rectángulo rojo (error/alerta) | Validación + flash / Notification |
| Morado (servicio externo) | Job + Mail / Payment gateway / Queue |
| Cilindro BD | Modelos Eloquent ya alineados a migraciones |

---

## FASE 0 — Fundación técnica (obligatoria antes de UI de producto)

> El diagrama asume usuarios, roles y sesión. Breeze usa `users`; tu BD usa `usuarios` + `roles` + `user_roles`. Sin esta fase, Login/Registro no son reales.

### Objetivo
Conectar Laravel al esquema StayFitt para autenticar contra `usuarios` y leer roles.

### Tablas
`usuarios`, `roles`, `user_roles`, `tokens_recuperacion`, `clientes`, `coaches`

### Qué desarrollar
1. Modelo `Usuario` (o adaptar `User`) apuntando a tabla `usuarios`, PK `id_user`, password en `password_hash`.
2. Modelos `Rol`, `UserRole` (o relaciones belongsToMany).
3. Configurar Auth guard / provider al modelo correcto.
4. Helpers o métodos: `tieneRol('ADMIN'|'COACH'|'CLIENTE'|...)`, `esCliente()`, `cliente()`, `coach()`.
5. Seeders de usuarios de prueba (1 admin, 1 coach, 1 cliente virtual, 1 cliente presencial).
6. Middleware base: `auth`, y esqueleto `EnsureRole` / `role:ADMIN`.

### Entregables
- [ ] Login funciona contra `usuarios`
- [ ] Registro crea fila en `usuarios` (+ rol `CLIENTE` o flujo controlado)
- [ ] Logout / reset password usan `tokens_recuperacion` o el flujo Breeze adaptado
- [ ] `php artisan tinker` puede resolver `$user->roles`

### Criterio de listo
Puedes autenticarte con un usuario seed y ver su rol en sesión/BD. **Aún no hace falta la Landing definitiva.**

---

## FASE 1 — Welcome / Landing (inicio del diagrama)

> **Empieza aquí a nivel de producto.** Coincide con el nodo superior: Visitante → Landing Page / Welcome.

### Objetivo
Página pública de entrada: marca StayFitt, CTA a planes y a Auth.

### Flujo del diagrama
`Visitante → Landing → Información Planes/Precios → ¿Usuario registrado?`

### Tablas (lectura pública)
`planes` (listado de planes activos; si aún no hay CRUD Admin, usa seed/datos de prueba)

### Qué desarrollar
1. Reemplazar `welcome` genérico por Landing StayFitt.
2. Secciones mínimas del diagrama: hero, planes/precios (resumen), info app, contacto (form o mailto).
3. CTAs: **Ver planes**, **Iniciar sesión**, **Registrarse**.
4. Layout público (`layouts/guest` o `layouts/public`) distinto del layout autenticado.
5. Rutas públicas: `/`, `/planes`, `/contacto` (o anclas en la misma página).

### Entregables
- [ ] `/` muestra Landing de marca (no el welcome Laravel por defecto)
- [ ] Enlaces a login y register
- [ ] Listado básico de planes (aunque sea estático al inicio; luego enlazar a `planes`)

### Criterio de listo
Un visitante entiende qué es StayFitt y puede ir a Login o Registro.

### Nota de orden
Puedes maquetar la Landing **en paralelo** con el final de la Fase 0, pero **no avances a dashboards** sin Auth real.

---

## FASE 2 — Auth completo (Login / Registro / Recuperación)

> Coincide con el bloque central superior del diagrama: Login, Registro, validación, recuperación.

### Objetivo
Cerrar el ciclo “¿Usuario registrado?” del diagrama.

### Flujo del diagrama
```
¿Registrado?
  NO → Formulario Registro → Validar → Crear usuario → (opcional verificar correo)
  SÍ → Login → Validar credenciales → Sesión
Errores: credenciales incorrectas / validación fallida
```

### Tablas
`usuarios`, `roles`, `user_roles`, `tokens_recuperacion`, `clientes` (perfil cliente al registrarse), `preferencias_cliente` (opcional)

### Qué desarrollar
1. **Registro**
   - Campos alineados a `usuarios` (+ `Tipo_documento` si aplica).
   - Crear `user_roles` (por defecto `CLIENTE`).
   - Crear registro en `clientes` (modalidad provisional o paso “elige VIRTUAL/PRESENCIAL”).
2. **Login**
   - Credenciales correo + password_hash.
   - Redirect post-login según rol (preparar para Fase 3).
3. **Recuperación de contraseña** con `tokens_recuperacion`.
4. **Verificación de correo** (si el diagrama lo exige vía `correo_verificado_en`).
5. Mensajes de error (nodos rojos del diagrama).

### Entregables
- [ ] Register + Login + Logout + Forgot/Reset password
- [ ] Validaciones Form Request
- [ ] Tras login, redirect provisional a `/dashboard` (luego se especializa)

### Criterio de listo
Visitante puede registrarse, entrar y salir. Fallos de auth se muestran correctamente.

---

## FASE 3 — Router por rol (diamante del diagrama)

> Tras Auth, el diagrama pregunta el rol: Admin / Coach / Cliente (y Cliente Virtual vs Presencial).

### Objetivo
Una sola puerta de entrada autenticada que enruta al panel correcto.

### Flujo del diagrama
```
Login OK → ¿Rol?
  ADMIN  → Panel Administrador
  COACH  → Dashboard Coach
  CLIENTE → ¿Modalidad?
      VIRTUAL    → Dashboard Cliente Virtual
      PRESENCIAL → Dashboard Cliente Presencial
```

### Tablas
`user_roles`, `roles`, `clientes.modalidad`, `clientes.tipo_cliente`, `coaches`

### Qué desarrollar
1. Ruta `/home` o `/dashboard` como **dispatcher** (no como UI final).
2. Middleware `role:ADMIN|COACH|CLIENTE`.
3. Middleware o policy `cliente.modalidad:VIRTUAL|PRESENCIAL`.
4. Layouts por rol:
   - `layouts/admin`
   - `layouts/coach`
   - `layouts/cliente` (con menú distinto Virtual/Presencial según módulos)
5. Dashboards **vacíos** (shell): solo menú + “Bienvenido”.

### Entregables
- [ ] Admin seed entra a `/admin`
- [ ] Coach seed entra a `/coach`
- [ ] Cliente virtual → `/cliente/virtual`
- [ ] Cliente presencial → `/cliente/presencial`
- [ ] Acceso cruzado bloqueado (403)

### Criterio de listo
El diamante de roles del diagrama funciona de punta a punta.

---

## FASE 4 — Catálogo y configuración Admin (base de negocio)

> El Admin del diagrama gestiona usuarios, planes, sedes/instituciones y roles. **Sin planes no hay compra ni acceso a módulos.**

### Objetivo
Que el Admin pueda crear los datos que el resto del flujo consume.

### Orden interno sugerido (dependencias BD)
1. Instituciones / “sedes”
2. Planes + módulos de servicio + `plan_modulos`
3. Coaches (vincular `usuarios` ↔ `coaches`)
4. Usuarios / asignación de roles
5. Horarios (necesario para presencial)
6. Cupones / enlaces institucionales (después de planes)

### Tablas
`instituciones`, `planes`, `modulos_servicio`, `plan_modulos`, `usuarios`, `user_roles`, `coaches`, `horarios`, `descuentos_cupones`, `enlaces_registro_institucional`

### Qué desarrollar (CRUD mínimo viable)
- [ ] CRUD Planes (precio, duración, modalidad, cupo, estado)
- [ ] Asignar módulos a un plan (`plan_modulos`) → esto controla qué ve el cliente
- [ ] CRUD Instituciones
- [ ] Alta de Coach (usuario + perfil coach)
- [ ] Listado usuarios + cambio de rol / estado
- [ ] CRUD Horarios (coach, días, cupo, modalidad)

### Criterio de listo
Existe al menos 1 plan Virtual y 1 plan Presencial con módulos asociados, 1 coach y 1 horario.

---

## FASE 5 — Flujo comercial: solicitud → pago → activación

> En el diagrama aparece como paso previo a usar el producto: el cliente elige plan, paga y obtiene acceso. Encaja entre Landing/Auth y el uso de módulos.

### Objetivo
Convertir visitante/cliente en **cliente con plan activo** y `acceso_cliente_modulo`.

### Flujo lógico
```
Ver plan → Solicitud compra → Pago (manual/pasarela) → Validación admin
  → planes_cliente activo → acceso_cliente_modulo → Cliente usa dashboard
```

### Tablas
`solicitudes_compra`, `pagos`, `facturas`, `devoluciones`, `planes_cliente`, `acceso_cliente_modulo`, `suscripciones_recurrentes`, `solicitudes_renovacion`, `descuentos_cupones`

### Qué desarrollar (por orden)
1. Cliente/Visitante autenticado solicita un plan (`solicitudes_compra`).
2. Registro de pago (`pagos`) — al inicio puede ser **comprobante manual** (sin pasarela).
3. Panel Admin: validar/rechazar pago (nodos verdes/rojos del diagrama).
4. Al validar: crear `planes_cliente` + generar `acceso_cliente_modulo` según `plan_modulos`.
5. Factura básica (`facturas`).
6. (Después) renovaciones, suscripciones, devoluciones, cupones.

### Entregables
- [ ] Solicitud de plan desde UI
- [ ] Admin valida pago
- [ ] Cliente ve plan vigente y módulos desbloqueados en menú

### Criterio de listo
Un cliente de prueba puede “comprar” un plan (flujo manual) y ver solo los módulos de su plan.

### Pasarela (morado del diagrama)
Dejar la integración Stripe/PayPal/PSE para **después** del flujo manual. Primero la lógica de negocio interna.

---

## FASE 6 — Shell de módulos del Cliente (menú según acceso)

### Objetivo
El dashboard del cliente solo muestra módulos con `acceso_cliente_modulo` activo.

### Tablas
`acceso_cliente_modulo`, `modulos_servicio`, `planes_cliente`

### Qué desarrollar
- [ ] Menú dinámico por código de módulo: `PERFIL`, `ENTRENAMIENTO`, `NUTRICION`, `CONTENIDO_VIRTUAL`, `PROGRESO`, `AGENDA`, `PAGOS`, `COMUNICACION`, `TIENDA`, `SESIONES_GRUPALES`
- [ ] Página “Sin acceso” si intenta entrar a un módulo no contratado
- [ ] Perfil cliente (editar datos de `usuarios` + `clientes`)

### Criterio de listo
El menú cambia según el plan; Perfil funciona.

---

## FASE 7 — Cliente Virtual (rama inferior izquierda del diagrama)

> Desarrollar **después** de tener plan + acceso. Orden interno = orden de consumo del diagrama.

### 7.1 Entrenamiento / Rutinas
**Tablas:** `planes_entrenamiento`, `rutinas`, `ejercicios`, `rutina_ejercicios`, `registro_rutinas`, `logs_ejercicio_detalle`, `material_entrenamiento`

- [ ] Ver plan de entrenamiento asignado
- [ ] Ver rutina del día + ejercicios
- [ ] Marcar rutina completada / log de series
- [ ] (Contenido lo crea el Coach en Fase 9; aquí solo consumo)

### 7.2 Contenido virtual (videos + progreso)
**Tablas:** `programas_virtuales`, `videos`, `categorias_video`, `progreso_videos`, `rachas_diarias`

- [ ] Listar programa/videos desbloqueados
- [ ] Reproductor + guardar progreso
- [ ] Rachas diarias

### 7.3 Nutrición
**Tablas:** `planes_nutricionales`, `comidas`, `consultas_ia_nutricion`

- [ ] Ver plan nutricional y comidas
- [ ] (Opcional más adelante) chat IA nutrición

### 7.4 Progreso
**Tablas:** `registros_progreso`, `progreso_fotos`, `metas_cliente`

- [ ] Registrar peso/medidas
- [ ] Subir fotos
- [ ] Ver gráficas / metas

### 7.5 Agenda / recordatorios (virtual)
**Tablas:** `recordatorios`, `eventos`, `evento_participantes`

- [ ] Recordatorios y eventos asignados

### Criterio de listo (Fase 7)
Cliente virtual con plan activo puede ver rutina, video, nutrición y registrar progreso (aunque el contenido lo cargue el admin/coach con seeds al inicio).

---

## FASE 8 — Cliente Presencial (rama inferior derecha del diagrama)

> Misma base de Auth/plan; módulos distintos: agenda física, asistencia, horarios.

### 8.1 Agenda y reservas
**Tablas:** `horarios`, `sesiones`, `sesion_participantes`, `disponibilidades`

- [ ] Ver horarios disponibles
- [ ] Reservar / cancelar sesión
- [ ] Ver mis citas

### 8.2 Asistencia / acceso
**Tablas:** `sesion_participantes` (estado asistencia)

- [ ] Check-in (QR puede ser fase posterior; primero marcado manual o código simple)
- [ ] Historial de asistencia

### 8.3 Evaluación / progreso presencial
Reutilizar `registros_progreso`, `metas_cliente`, valoraciones.

- [ ] Ver evaluaciones cargadas por coach
- [ ] Valoración al coach (`valoraciones_coach`)

### 8.4 Eventos / encuestas
**Tablas:** `eventos`, `evento_participantes`, `encuestas_satisfaccion`

### Criterio de listo (Fase 8)
Cliente presencial reserva sesión y ve su asistencia/progreso.

---

## FASE 9 — Dashboard Coach (crear lo que el cliente consume)

> En el diagrama, el Coach alimenta rutinas, dietas, seguimiento y agenda. Lógicamente va **después** de los shells del cliente, o en paralelo a las Fases 7–8 (recomendado: **paralelo controlado**).

### Orden interno Coach
1. Lista de clientes asignados (`clientes.id_coach`)
2. Crear/editar ejercicios y rutinas → asignar plan de entrenamiento
3. Nutrición → asignar plan nutricional
4. Seguimiento de progreso de sus clientes
5. Agenda: disponibilidades + sesiones + pasar lista
6. Chat con cliente (Fase 11)
7. Material de entrenamiento

### Tablas principales
`coaches`, `clientes`, `ejercicios`, `rutinas`, `rutina_ejercicios`, `planes_entrenamiento`, `planes_nutricionales`, `comidas`, `disponibilidades`, `sesiones`, `sesion_participantes`, `registros_progreso`, `metas_cliente`, `recordatorios`

### Entregables
- [ ] Coach ve solo sus clientes
- [ ] Puede asignar rutina y dieta
- [ ] Puede crear sesión y marcar asistencia
- [ ] Puede registrar progreso de un cliente

### Criterio de listo
El flujo Coach → Cliente del diagrama funciona sin seeds manuales en SQL.

---

## FASE 10 — Admin avanzado (operación y gobierno)

> Completa el panel Admin del diagrama más allá del catálogo inicial.

### Qué desarrollar
- [ ] Validación de pagos / devoluciones / facturas (si quedó pendiente)
- [ ] Reportes (`reportes_generados` + vistas `vw_*`)
- [ ] Bitácora (`bitacora_sistema`)
- [ ] Gestión de solicitudes de renovación
- [ ] Enlaces de registro institucional
- [ ] Configuración de estados de cliente (`historial_estados_cliente`)
- [ ] Programas virtuales y catálogo de videos (si no lo hace el coach)

### Criterio de listo
Admin puede operar el negocio día a día sin tocar la BD a mano.

---

## FASE 11 — Transversales (aparecen en varias ramas del diagrama)

> No bloquean el MVP temprano, pero sí el producto “integral” del diagrama.

### 11.1 Notificaciones
**Tabla:** `notificaciones`  
Triggers: rutina nueva, recordatorio de sesión, pago validado, mensaje nuevo.

### 11.2 Chat / comunicación
**Tablas:** `chats`, `chat_participantes`, `mensajes`  
Orden: chat 1:1 cliente–coach → luego grupos/comunidad.

### 11.3 Tienda FigueFit
**Tablas:** `categorias_producto`, `productos`, `direcciones_envio`, `pedidos`, `pedido_items`, `pagos` (origen TIENDA)

### 11.4 Encuestas y valoraciones
Ya parcialmente tocadas; cerrar UX completa.

### Criterio de listo
Notificaciones + chat básico funcionando entre coach y cliente.

---

## FASE 12 — Integraciones externas y endurecimiento

> Nodos morados / API del encabezado del diagrama.

1. Pasarela de pagos real
2. Emails transaccionales (colas)
3. Storage de fotos/comprobantes/videos (local → S3)
4. API REST/Sanctum si habrá app móvil
5. QR de acceso presencial
6. IA nutrición (`consultas_ia_nutricion`)
7. Tests Pest de flujos críticos (auth, pago, acceso módulos)
8. Roles institucionales (`CLIENTE_INSTITUCIONAL`, sesiones grupales)

---

## Mapa rápido: diagrama → fase

| Bloque del diagrama | Fase |
|---------------------|------|
| Visitante / Landing / Planes públicos | 1 (+ lectura planes de 4) |
| Login / Registro / Recuperación | 0 + 2 |
| Decisión de rol Admin/Coach/Cliente | 3 |
| Panel Administrador (gestión base) | 4 → 10 |
| Compra / pago / activación | 5 |
| Dashboard Cliente Virtual | 6 + 7 |
| Dashboard Cliente Presencial | 6 + 8 |
| Dashboard Coach | 9 |
| Chat / notificaciones / tienda | 11 |
| Pasarelas / API / IA / QR | 12 |

---

## Roadmap MVP recomendado (para no ahogarte)

Si quieres una primera versión usable rápido, cierra solo esto:

1. **Fase 0** — Auth real con `usuarios`
2. **Fase 1** — Landing
3. **Fase 2–3** — Login + router de roles + shells
4. **Fase 4 (mínima)** — CRUD planes + asignar módulos + 1 coach
5. **Fase 5 (manual)** — solicitud + pago con comprobante + validación admin
6. **Fase 6** — menú por módulos + perfil
7. **Fase 9 (mínima)** — coach asigna 1 rutina
8. **Fase 7.1** — cliente virtual ve y completa rutina

Con eso ya recorres el **esqueleto vertical del diagrama**. Luego amplías nutrición, videos, presencial, chat y tienda.

---

## Orden de carpetas / convención Laravel sugerida

```
app/
  Models/           # Usuario, Rol, Cliente, Coach, Plan, ...
  Http/
    Controllers/
      Web/          # Landing, Contacto
      Auth/         # ya existe (Breeze) — adaptar
      Admin/
      Coach/
      Cliente/
    Middleware/     # EnsureRole, EnsureClienteModalidad, EnsureModuloAcceso
    Requests/
  Services/         # PagoService, AccesoModuloService, RachaService, ...
resources/views/
  public/           # landing, planes
  admin/
  coach/
  cliente/virtual/
  cliente/presencial/
  components/
routes/
  web.php           # públicos + dispatcher
  admin.php
  coach.php
  cliente.php
  auth.php
```

---

## Reglas de coherencia (no romper el diagrama)

1. **Nunca** construyas módulos de cliente antes del router de roles.
2. **Nunca** desbloquees entrenamiento/nutrición sin `acceso_cliente_modulo`.
3. **Admin crea catálogo → Coach asigna contenido → Cliente consume.**
4. **Presencial depende de horarios/sesiones**; Virtual depende de programas/rutinas/videos.
5. La Landing puede existir sin pagos; **el producto autenticado no.**
6. Integra pasarelas al final del flujo de pagos interno, no al inicio.
7. Reutiliza Breeze para pantallas de Auth; **no reinventes login**, adáptalo al esquema.

---

## Checklist maestro (progreso global)

- [ ] Fase 0 — Fundación Auth + modelos
- [ ] Fase 1 — Welcome / Landing
- [ ] Fase 2 — Auth completo
- [ ] Fase 3 — Router por rol + shells
- [ ] Fase 4 — Catálogo Admin MVP
- [ ] Fase 5 — Compra / pago / activación
- [ ] Fase 6 — Menú módulos + perfil
- [ ] Fase 7 — Cliente Virtual
- [ ] Fase 8 — Cliente Presencial
- [ ] Fase 9 — Coach
- [ ] Fase 10 — Admin avanzado
- [ ] Fase 11 — Chat / notificaciones / tienda
- [ ] Fase 12 — Integraciones y hardening

---

## Conclusión operativa

**Empieza por la Fundación (Fase 0) y el Welcome (Fase 1), luego Auth (Fase 2) y el router de roles (Fase 3).**  
Eso es exactamente la columna superior del diagrama. Todo lo demás cuelga de ahí.

Si mañana abres el IDE y solo puedes hacer una cosa: **adapta Auth a `usuarios`/`roles` y deja la Landing enlazando a Login/Registro.** Esa es la primera milla coherente con StayFitt v3.0.
