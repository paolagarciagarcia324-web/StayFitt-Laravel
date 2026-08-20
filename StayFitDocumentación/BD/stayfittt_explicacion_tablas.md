# StayFit — Explicación de tablas de la base de datos `stayfittt`

**Proyecto:** StayFit v4.0 (FigueFit)  
**Base de datos:** `stayfit`  
**Propósito de este documento:** describir **qué hace cada tabla en el sistema** y **qué información guarda**, para que cualquier integrante del equipo pueda entender la persistencia sin leer el SQL.

El modelo cubre el ciclo completo del software: autenticación, clientes (virtual, presencial e institucional), planes y cupos, pagos (pasarela y comprobante), activación de accesos, entrenamiento, nutrición con IA, videos con validación por IA, progreso físico, agenda, comunicación (chat interno y WhatsApp), tienda presencial, reportes y auditoría.

Las tablas internas del motor (`sqlite_sequence`) no se documentan: no forman parte del dominio StayFit.

---

## Cómo leer este documento

Cada entrada responde dos preguntas:

1. **Función en el sistema:** para qué existe la tabla en el flujo de negocio.
2. **Qué guarda:** los datos que persiste (concepto, no el listado de cada columna).

Al final se describen las **vistas**, que no almacenan datos propios: consultan tablas reales para reportes y reglas de cupo.

---

## 1. Seguridad y usuarios

### `roles`

**Función:** define los perfiles de autorización de StayFit.  
**Qué guarda:** el catálogo de roles (`ADMIN`, `COACH`, `CLIENTE`, `CLIENTE_INSTITUCIONAL`), su nombre, descripción y si está activo.

### `user`

**Función:** es la cuenta de autenticación (correo y contraseña). Un visitante no se convierte en cliente activo solo por existir aquí: primero debe completar el flujo de pago.  
**Qué guarda:** nombres, apellidos, correo, hash de contraseña, teléfono, foto de perfil, estado de la cuenta (`PENDIENTE`, `ACTIVO`, `INACTIVO`, `BLOQUEADO`), verificación de correo y último acceso.

### `user_roles`

**Función:** relaciona usuarios con roles (N:M). Un mismo usuario puede tener más de un rol si el negocio lo requiere.  
**Qué guarda:** qué usuario tiene qué rol, cuándo se asignó y quién lo asignó.

### `tokens_recuperacion`

**Función:** soporta el restablecimiento seguro de contraseña (RF-005).  
**Qué guarda:** el token temporal asociado al usuario, su fecha de expiración, si ya se usó y datos de la solicitud (IP).

### `sesiones_auth`

**Función:** deja evidencia de inicios de sesión y permite regenerar/cerrar sesiones (prevención de fijación de sesión).  
**Qué guarda:** usuario, identificador de sesión de la aplicación, IP, navegador, hora de inicio y de cierre.

---

## 2. Instituciones, coaches y clientes

### `instituciones`

**Función:** administra convenios y programas institucionales (RF-054) sin reemplazar la identidad individual del cliente.  
**Qué guarda:** nombre, NIT, contacto, ciudad, convenio, cupos totales y usados, vigencia y estado (`ACTIVA`, `INACTIVA`, `VENCIDA`).

### `coaches`

**Función:** perfil operativo del profesional que acompaña clientes (RF-003).  
**Qué guarda:** vínculo con `user`, especialidad, biografía, certificaciones, foto, calificación promedio, total de valoraciones y estado del coach.

### `clientes`

**Función:** es el expediente de negocio de quien recibe el servicio. Distingue individual vs institucional y modalidad Virtual / Presencial / Mixto.  
**Qué guarda:** documento, datos demográficos, objetivo fitness, salud (alergias, lesiones, medicamentos, contacto de emergencia), institución y coach asignados, estado (`PROSPECTO`, `PENDIENTE_PAGO`, `ACTIVO`, etc.) y fecha de activación.

### `preferencias_cliente`

**Función:** personaliza notificaciones y privacidad del cliente (RF-008).  
**Qué guarda:** canales de aviso (correo, push, WhatsApp), horario preferido, minutos de recordatorio, idioma y quién puede ver fotos de progreso.

### `historial_estados_cliente`

**Función:** auditoría obligatoria cada vez que cambia `estado_cliente`.  
**Qué guarda:** estado anterior y nuevo, motivo, responsable y fecha del cambio.

### `historial_asignacion_coach`

**Función:** trazabilidad de RF-009 cuando el administrador asigna o cambia el coach.  
**Qué guarda:** cliente, coach anterior, coach nuevo, administrador, motivo y fecha.

### `valoraciones_coach`

**Función:** alimenta la reputación del coach (`calificacion_promedio` y `total_valoraciones`).  
**Qué guarda:** calificación 1–5, comentario y qué cliente valoró a qué coach.

### `medidas_antropometricas_iniciales`

**Función:** línea base del progreso en el alta presencial (RF-053).  
**Qué guarda:** peso, estatura, cintura, cadera, brazo y pierna del registro inicial; el IMC se calcula automáticamente.

---

## 3. Planes comerciales, módulos y cupones

### `planes`

**Función:** catálogo expuesto en Welcome (RF-034). Controla precio, duración, modalidad y **cupos**.  
**Qué guarda:** código, nombre, descripción, modalidad, precio en COP, días de vigencia, cupos totales/reservados/ocupados, inclusiones, beneficios y si admite renovación.

### `modulos_servicio`

**Función:** define las coberturas que un plan puede habilitar (entrenamiento, nutrición, videos, tienda, etc.).  
**Qué guarda:** código, nombre y si el módulo es exclusivo de Virtual, Presencial o aplica a todas las modalidades.

### `plan_modulos`

**Función:** indica qué módulos incluye cada plan comercial. Es la base para generar `acceso_cliente_modulo` al activar.  
**Qué guarda:** la relación plan–módulo y si está incluido.

### `enlaces_registro_institucional`

**Función:** alta de clientes institucionales con token único (RF-055).  
**Qué guarda:** institución, plan asociado, token, cupos máximos, usos, fecha de expiración y estado del enlace.

### `descuentos_cupones`

**Función:** permite aplicar códigos de descuento en la compra.  
**Qué guarda:** código, tipo (porcentaje o monto), valor, vigencia, usos máximos/actuales y, si aplica, el plan al que está restringido.

### `usos_cupones`

**Función:** evita usos indebidos y deja rastro de cada redención.  
**Qué guarda:** qué cupón se usó, en qué solicitud y con qué correo.

---

## 4. Compra, pagos, facturación y acceso

Este bloque es el corazón de la trazabilidad comercial: **solicitud → pago → plan del cliente → accesos**.

### `solicitudes_compra`

**Función:** registra el intento de compra **antes** de que la persona sea cliente activo. Las solicitudes `PENDIENTE` y `VALIDADA` reservan cupo; `RECHAZADA` o `CANCELADA` lo liberan.  
**Qué guarda:** plan elegido, datos del comprador, origen (pasarela, comprobante, admin, institucional, renovación), totales, estado, reserva de cupo y observación del administrador si revisa un comprobante.

### `planes_cliente`

**Función:** es el contrato de servicio vigente: qué plan tiene el cliente y hasta cuándo (RF-030, RF-033).  
**Qué guarda:** cliente, plan comercial, fechas de inicio y fin, estado (`ACTIVO`, `VENCIDO`, etc.), renovación automática y vínculos a solicitud, pago y suscripción.

### `pagos`

**Función:** recaudo de planes, renovaciones y tienda. Distingue pasarela (validación automática) de comprobante (validación administrativa).  
**Qué guarda:** monto, método, pasarela, referencia, URL del comprobante, estado (`PENDIENTE`, `VALIDADO`, `RECHAZADO`, `ANULADO`), fechas y observación del validador.

### `facturas`

**Función:** comprobante fiscal/descargable tras un pago validado (RF-032).  
**Qué guarda:** número de factura, tipo (plan o tienda), datos del cliente al momento de emitir, subtotal, impuestos, total y rutas de PDF/XML.

### `suscripciones_recurrentes`

**Función:** cobros periódicos (mensual, trimestral, semestral, anual) cuando hay renovación automática.  
**Qué guarda:** cliente, plan, periodicidad, token de la pasarela, próximo cobro y estado de la suscripción.

### `acceso_cliente_modulo`

**Función:** autorización real de uso. El cliente **solo** entra a un módulo si aquí está `ACTIVO` y vigente (RF-031, RF-035).  
**Qué guarda:** cliente, módulo, plan_cliente asociado, estado del acceso y fechas de vigencia.

### `configuracion_pasarelas`

**Función:** parametriza Wompi, PayU, MercadoPago, Nequi y Daviplata (RF-050).  
**Qué guarda:** modo sandbox/producción, identificadores de comercio, secretos cifrados, URLs de checkout y webhook, y si la pasarela está activa. No debe almacenar claves en texto plano.

### `transacciones_pasarela`

**Función:** puente técnico entre StayFit y la pasarela. Soporta webhook, firma e **idempotencia** para no activar dos veces el mismo pago.  
**Qué guarda:** referencia interna/externa, monto, estado de la pasarela (`CREATED`, `PENDING`, `APPROVED`, `DECLINED`, `CANCELLED`, `EXPIRED`, `ERROR`), clave de idempotencia, payloads y si la activación ya se ejecutó.

### `eventos_webhook`

**Función:** bitácora de cada notificación HTTP recibida de la pasarela.  
**Qué guarda:** pasarela, id externo del evento, headers, payload, firma recibida, si la firma fue válida y si el evento ya se procesó.

### `conciliaciones_pasarela`

**Función:** job de respaldo cuando el webhook no llega (consulta cada cierto tiempo).  
**Qué guarda:** transacción consultada, estado reportado por la pasarela, si coincide con StayFit y la respuesta cruda.

### `intentos_activacion`

**Función:** reintentos controlados de activación (cuenta, plan, accesos, factura) sin duplicar registros.  
**Qué guarda:** número de intento, resultado (`OK`, `ERROR`, `DUPLICADO_EVITADO`) y detalle del error.

---

## 5. Programas virtuales y videos

### `programas_virtuales`

**Función:** agrupa el material pregrabado de modalidad Virtual.  
**Qué guarda:** nombre, descripción, plan comercial asociado y estado.

### `categorias_video`

**Función:** organiza los videos dentro de un programa (por bloque o semana).  
**Qué guarda:** programa, nombre de la categoría y orden de presentación.

### `videos`

**Función:** cada clase o sesión de video con desbloqueo progresivo por día.  
**Qué guarda:** día de orden, título, descripción, URL, duración y si requiere validación por IA.

### `progreso_videos`

**Función:** avance del cliente: candado, desbloqueo, en curso o completado, y llama de racha.  
**Qué guarda:** cliente, video, estado, fechas de inicio/cierre, porcentaje y si se otorgó racha.

### `validaciones_ia_video`

**Función:** resultado de la IA de postura y repeticiones al reproducir el video.  
**Qué guarda:** repeticiones detectadas/correctas, alertas de postura, resultado (`PENDIENTE`, `APROBADO`, `RECHAZADO`) y detalle técnico.

---

## 6. Entrenamiento y asistencia

### `planes_entrenamiento`

**Función:** plan de trabajo que arma el coach y, si aplica, envía a validación del administrador.  
**Qué guarda:** cliente, coach, nombre, objetivo, modalidad y estado (`BORRADOR`, `PENDIENTE_VALIDACION`, `ACTIVO`, etc.).

### `rutinas`

**Función:** sesiones concretas dentro de un plan de entrenamiento.  
**Qué guarda:** nombre, descripción, día de la semana, orden, duración y estado.

### `ejercicios`

**Función:** catálogo reutilizable de ejercicios.  
**Qué guarda:** nombre, grupo muscular, descripción e instrucciones.

### `rutina_ejercicios`

**Función:** detalle de prescripción: qué ejercicios van en cada rutina.  
**Qué guarda:** series, repeticiones, descanso, peso sugerido, RIR, ritmo de ejecución, orden y notas.

### `material_entrenamiento`

**Función:** apoyo multimedia de rutinas y ejercicios (RF-012).  
**Qué guarda:** tipo (texto, imagen, video, PDF, enlace), título, URL o contenido.

### `registro_rutinas`

**Función:** el cliente marca la rutina como hecha (RF-014).  
**Qué guarda:** fecha de ejecución, si se completó, dificultad percibida (1–5) y observación del cliente.

### `logs_ejercicio_detalle`

**Función:** bitácora fina de lo que realmente hizo en cada ejercicio.  
**Qué guarda:** series y repeticiones realizadas, peso, RIR real y nota.

### `sesiones`

**Función:** entrenamientos programados en agenda (individual/grupal, virtual/presencial).  
**Qué guarda:** coach, tipo, título, fecha, horario, modalidad, cupo y estado (`PROGRAMADA`, `COMPLETADA`, `CANCELADA`, etc.).

### `asistencias`

**Función:** control de asistencia presencial; alimenta la llama del calendario.  
**Qué guarda:** cliente, coach, sesión opcional, fecha, estado (`ASISTIO`, `INASISTENCIA`, `JUSTIFICADA`) y si genera racha.

### `rachas_cliente`

**Función:** contador de días consecutivos (videos, asistencia o rutinas), estilo Duolingo.  
**Qué guarda:** tipo de racha, días actuales, mejor racha histórica y última fecha contabilizada.

---

## 7. Nutrición e inteligencia artificial

### `planes_nutricionales`

**Función:** guía alimentaria asociada al objetivo y plan del cliente (RF-017 a RF-020).  
**Qué guarda:** nombre, objetivo, calorías y macros objetivo, recomendaciones y estado.

### `comidas`

**Función:** desglose del plan por tiempo de comida.  
**Qué guarda:** tipo (desayuno, almuerzo, etc.), receta, porción, macros, alérgenos y vitaminas/minerales.

### `consultas_ia_nutricion`

**Función:** historial del chat de nutrición con IA (RF-021), visible para seguimiento del coach.  
**Qué guarda:** foto enviada, alimentos detectados, si cumple la estructura del plan (alerta verde/roja), macros estimados, alternativas sugeridas y respuesta de la IA.

---

## 8. Progreso físico, metas y recordatorios

### `registros_progreso`

**Función:** evolución periódica (peso, medidas, métricas de salud y observaciones del coach). Se agrupa por plan para ver “carpetas” de periodo.  
**Qué guarda:** fecha, antropometría completa, IMC, signos vitales, energía/sueño/estrés, agua y si el registro es línea base.

### `fotos_progreso`

**Función:** evidencia fotográfica ligada a un registro (frente, perfil, espalda).  
**Qué guarda:** ángulo, URL de la foto y fecha de carga.

### `metas_cliente`

**Función:** metas SMART; el porcentaje de avance se calcula solo.  
**Qué guarda:** métrica, valor inicial/actual/objetivo, unidad, fechas y estado (`ACTIVA`, `LOGRADA`, `CANCELADA`).

### `recordatorios`

**Función:** avisos automáticos de entrenamiento, comidas, sesiones, pagos, hidratación o sueño (RF-042).  
**Qué guarda:** destinatario, tipo, mensaje, recurrencia, fecha/hora y si está activo.

---

## 9. Agenda, eventos y satisfacción

### `disponibilidades`

**Función:** horarios en los que el coach puede agendar (RF-045).  
**Qué guarda:** día de la semana, franja horaria, modalidad y si el bloque está activo.

### `sesion_participantes`

**Función:** inscripción de clientes a una sesión respetando cupos (RF-044).  
**Qué guarda:** sesión, cliente y estado (`INSCRITO`, `CONFIRMADO`, `ASISTIO`, `AUSENTE`, `CANCELADO`).

### `eventos`

**Función:** talleres, retos y actividades especiales (RF-043).  
**Qué guarda:** nombre, tipo, fechas, modalidad, cupos, estado y quién lo creó (coach o admin).

### `evento_participantes`

**Función:** control de inscritos al evento.  
**Qué guarda:** evento, cliente y estado de participación.

### `encuestas_satisfaccion`

**Función:** calificación del plan al cierre o en hitos de seguimiento.  
**Qué guarda:** estrellas 1–5, comentario y si recomienda el plan.

---

## 10. Comunicación: chat virtual, notificaciones y WhatsApp presencial

### `chats`

**Función:** salas de chat **interno** para clientes Virtuales: individual (cliente–coach) o comunidad del plan (RF-036).  
**Qué guarda:** tipo, plan, coach, nombre y estado.

### `chat_participantes`

**Función:** quién pertenece a cada chat, incluyendo supervisión del administrador.  
**Qué guarda:** chat, usuario y rol en el chat (`COACH`, `CLIENTE`, `ADMIN_SUPERVISOR`).

### `mensajes`

**Función:** contenido del chat en tiempo real.  
**Qué guarda:** emisor, texto o adjunto, tipo de mensaje y si fue leído.

### `notificaciones`

**Función:** avisos de pagos, vencimientos, rutinas, agenda, motivación, tienda o WhatsApp (RF-037 a RF-039).  
**Qué guarda:** destinatario, tipo, título, mensaje, canal (`IN_APP`, `EMAIL`, `PUSH`, `WHATSAPP`) y si ya se leyó.

### `configuracion_whatsapp`

**Función:** parámetros de WhatsApp Business para clientes Presenciales (RF-052).  
**Qué guarda:** número, token cifrado, link del grupo principal, mensaje de bienvenida, horario de atención y respuestas automáticas.

### `grupos_whatsapp`

**Función:** grupos por horario, plan o modalidad.  
**Qué guarda:** nombre, criterio (mañana/tarde, básico/premium, etc.), link de invitación y estado.

### `participantes_grupo_whatsapp`

**Función:** quién fue invitado o unido a cada grupo.  
**Qué guarda:** grupo, cliente y estado (`INVITADO`, `UNIDO`, `ELIMINADO`).

### `invitaciones_whatsapp`

**Función:** evidencia del alert flotante que aparece al terminar el registro presencial.  
**Qué guarda:** cliente, link mostrado, cuándo se mostró, si se aceptó o se cerró sin unirse.

---

## 11. Tienda e-commerce (solo presenciales)

La tienda no está disponible para visitantes ni para clientes Virtuales.

### `categorias_producto`

**Función:** agrupa el catálogo FigueFit (camisetas, accesorios, suplementos, marca).  
**Qué guarda:** nombre, descripción y estado.

### `productos_tienda`

**Función:** ficha comercial del producto (RF-051).  
**Qué guarda:** SKU, nombre, descripción, precio, imagen, stock total, umbral de stock bajo y bandera de exclusividad presencial.

### `producto_variantes`

**Función:** inventario por talla y color.  
**Qué guarda:** talla, color, SKU de variante y stock de esa combinación.

### `carritos`

**Función:** carrito de compra del cliente presencial.  
**Qué guarda:** cliente y estado (`ABIERTO`, `CONVERTIDO`, `ABANDONADO`).

### `carrito_items`

**Función:** líneas del carrito antes del checkout.  
**Qué guarda:** producto, variante, cantidad y precio unitario.

### `ordenes_tienda`

**Función:** pedido confirmable tras el pago por pasarela.  
**Qué guarda:** número de orden, totales, tipo de entrega (sede, envío local o nacional), dirección, estado (`PENDIENTE_PAGO`, `PAGADA`, `PREPARADA`, `ENVIADA`, `ENTREGADA`, `CANCELADA`) y pago asociado.

### `orden_detalle`

**Función:** snapshot de lo comprado, independiente de cambios posteriores de catálogo.  
**Qué guarda:** producto, variante, cantidad, precio y subtotal de cada línea.

### `alertas_stock`

**Función:** avisa cuando el inventario baja del umbral.  
**Qué guarda:** producto/variante, stock actual, umbral y momento de la alerta.

---

## 12. Reportes, auditoría, configuración y trazabilidad documental

### `reportes_generados`

**Función:** historial de reportes PDF/Excel pedidos por coach o administrador (RF-046 a RF-049).  
**Qué guarda:** quién lo generó, tipo, parámetros, formato y URL del archivo.

### `bitacora_sistema`

**Función:** auditoría de operaciones críticas (pagos, accesos, usuarios, cambios administrativos).  
**Qué guarda:** usuario, acción, entidad, id afectado, datos antes/después, IP y resultado.

### `configuracion_sistema`

**Función:** parámetros globales de la plataforma (nombre, moneda, umbral de últimos cupos, flags de tienda y chat).  
**Qué guarda:** pares clave–valor y una descripción de para qué sirve cada parámetro.

### `matriz_trazabilidad`

**Función:** puente entre análisis y base de datos: cada RF queda ligado a su HU, CU y tablas.  
**Qué guarda:** identificadores RF-001 a RF-055, historia, caso de uso, módulo, actor y tablas relacionadas.

---

## Vistas (consultas, no almacenamiento)

Las vistas no “guardan” información: calculan lecturas frecuentes.

| Vista | Para qué sirve |
| --- | --- |
| `vw_cupos_planes` | Estado comercial del cupo: `DISPONIBLE`, `ULTIMOS_CUPOS`, `CUPO_LLENO` o `INACTIVO`. |
| `vw_clientes_activos` | Cliente + plan vigente + coach e institución. |
| `vw_pagos_por_estado` | Pagos cruzados con solicitud y estado de pasarela. |
| `vw_accesos_vigentes` | Si el módulo está realmente habilitado hoy. |
| `vw_progreso_resumen` | Primera/última fecha y rango de peso por cliente y plan. |
| `vw_ingresos_pasarela` | Recaudo agrupado por pasarela, método, estado y fecha. |
| `vw_asistencia_racha` | Días consecutivos y mejor racha por cliente. |

---

## Mapa rápido proceso → tablas

| Proceso del software | Tablas principales |
| --- | --- |
| Login y roles | `user`, `roles`, `user_roles`, `tokens_recuperacion`, `sesiones_auth` |
| Alta y perfil | `clientes`, `preferencias_cliente`, `medidas_antropometricas_iniciales` |
| Instituciones | `instituciones`, `enlaces_registro_institucional` |
| Compra y cupos | `planes`, `solicitudes_compra`, `descuentos_cupones`, `usos_cupones` |
| Pago y activación | `pagos`, `transacciones_pasarela`, `eventos_webhook`, `planes_cliente`, `acceso_cliente_modulo`, `facturas` |
| Entrenamiento | `planes_entrenamiento`, `rutinas`, `ejercicios`, `registro_rutinas` |
| Nutrición + IA | `planes_nutricionales`, `comidas`, `consultas_ia_nutricion` |
| Videos + IA | `programas_virtuales`, `videos`, `progreso_videos`, `validaciones_ia_video` |
| Progreso | `registros_progreso`, `fotos_progreso`, `metas_cliente` |
| Agenda | `disponibilidades`, `sesiones`, `eventos`, `recordatorios`, `asistencias` |
| Chat virtual | `chats`, `mensajes`, `notificaciones` |
| WhatsApp presencial | `configuracion_whatsapp`, `grupos_whatsapp`, `invitaciones_whatsapp` |
| Tienda presencial | `productos_tienda`, `carritos`, `ordenes_tienda`, `orden_detalle` |
| Auditoría | `bitacora_sistema`, `historial_estados_cliente`, `reportes_generados` |

---

*Documento generado a partir del esquema `stayfittt` alineado con StayFit v4.0 (SRS, historias de usuario y casos de uso).*
