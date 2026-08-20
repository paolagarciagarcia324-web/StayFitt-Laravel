-- =============================================================================
-- STAYFIT — Base de datos StayFit v4.0 (FigueFit)
-- Motor: MySQL 8.0+ / MariaDB 10.6+
-- Cobertura: RF-001 a RF-055, CU-01 a CU-55, HU alineadas, reglas de negocio v4.0
-- Trazabilidad: compra, pagos (pasarela/comprobante), activación, cupos, accesos,
--               entrenamiento, nutrición+IA, videos+IA, progreso, agenda, chat,
--               WhatsApp presencial, tienda presencial, instituciones, auditoría.
-- =============================================================================

DROP DATABASE IF EXISTS stayfit;
CREATE DATABASE stayfit
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE stayfit;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- SECCIÓN 1 — Seguridad y usuarios (RF-001 a RF-006)
-- -----------------------------------------------------------------------------

CREATE TABLE roles (
  id_rol            SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_rol        VARCHAR(40) NOT NULL,
  nombre_rol        VARCHAR(80) NOT NULL,
  descripcion       VARCHAR(255) NULL,
  estado            ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_rol),
  UNIQUE KEY uq_roles_codigo (codigo_rol)
) ENGINE=InnoDB COMMENT='Roles ADMIN, COACH, CLIENTE, CLIENTE_INSTITUCIONAL';

CREATE TABLE `user` (
  id_user           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombres           VARCHAR(80) NOT NULL,
  apellidos         VARCHAR(80) NOT NULL,
  correo            VARCHAR(150) NOT NULL,
  password_hash     VARCHAR(255) NOT NULL,
  telefono          VARCHAR(30) NULL,
  foto_perfil_url   VARCHAR(500) NULL,
  estado_cuenta     ENUM('PENDIENTE','ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'PENDIENTE',
  email_verificado_en DATETIME NULL,
  ultimo_acceso     DATETIME NULL,
  remember_token    VARCHAR(100) NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user),
  UNIQUE KEY uq_user_correo (correo),
  KEY idx_user_estado (estado_cuenta)
) ENGINE=InnoDB COMMENT='Cuentas de autenticación Laravel Breeze';

CREATE TABLE user_roles (
  id_user_rol       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user           INT UNSIGNED NOT NULL,
  id_rol            SMALLINT UNSIGNED NOT NULL,
  asignado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  asignado_por      INT UNSIGNED NULL,
  PRIMARY KEY (id_user_rol),
  UNIQUE KEY uq_user_rol (id_user, id_rol),
  CONSTRAINT fk_ur_user FOREIGN KEY (id_user) REFERENCES `user` (id_user),
  CONSTRAINT fk_ur_rol FOREIGN KEY (id_rol) REFERENCES roles (id_rol),
  CONSTRAINT fk_ur_asignado FOREIGN KEY (asignado_por) REFERENCES `user` (id_user)
) ENGINE=InnoDB;

CREATE TABLE tokens_recuperacion (
  id_token          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user           INT UNSIGNED NOT NULL,
  token_hash        VARCHAR(255) NOT NULL,
  expira_en         DATETIME NOT NULL,
  usado_en          DATETIME NULL,
  ip_solicitud      VARCHAR(45) NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_token),
  KEY idx_token_user (id_user),
  KEY idx_token_expira (expira_en),
  CONSTRAINT fk_token_user FOREIGN KEY (id_user) REFERENCES `user` (id_user)
) ENGINE=InnoDB COMMENT='RF-005 recuperación de contraseña';

CREATE TABLE sesiones_auth (
  id_sesion_auth    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user           INT UNSIGNED NOT NULL,
  id_sesion_app     VARCHAR(128) NOT NULL,
  ip                VARCHAR(45) NULL,
  user_agent        VARCHAR(255) NULL,
  iniciada_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cerrada_en        DATETIME NULL,
  PRIMARY KEY (id_sesion_auth),
  KEY idx_sesauth_user (id_user),
  CONSTRAINT fk_sesauth_user FOREIGN KEY (id_user) REFERENCES `user` (id_user)
) ENGINE=InnoDB COMMENT='Regeneración de sesión y último acceso';

-- -----------------------------------------------------------------------------
-- SECCIÓN 2 — Instituciones, coaches y clientes (RF-002, RF-003, RF-007..009, RF-053..055)
-- -----------------------------------------------------------------------------

CREATE TABLE instituciones (
  id_institucion    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(150) NOT NULL,
  nit               VARCHAR(30) NULL,
  contacto_nombre   VARCHAR(120) NULL,
  correo            VARCHAR(150) NULL,
  telefono          VARCHAR(30) NULL,
  ciudad            VARCHAR(80) NULL,
  direccion         VARCHAR(200) NULL,
  convenio          VARCHAR(200) NULL,
  cupos_totales     INT UNSIGNED NOT NULL DEFAULT 0,
  cupos_usados      INT UNSIGNED NOT NULL DEFAULT 0,
  fecha_inicio      DATE NULL,
  fecha_fin         DATE NULL,
  estado            ENUM('ACTIVA','INACTIVA','VENCIDA') NOT NULL DEFAULT 'ACTIVA',
  observaciones     TEXT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_institucion),
  UNIQUE KEY uq_inst_nit (nit),
  KEY idx_inst_estado (estado)
) ENGINE=InnoDB COMMENT='RF-054 gestión de instituciones';

CREATE TABLE coaches (
  id_coach          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user           INT UNSIGNED NOT NULL,
  especialidad      VARCHAR(120) NULL,
  biografia         TEXT NULL,
  certificaciones   VARCHAR(500) NULL,
  foto_url          VARCHAR(500) NULL,
  calificacion_promedio DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  total_valoraciones INT UNSIGNED NOT NULL DEFAULT 0,
  estado_coach      ENUM('ACTIVO','INACTIVO','SUSPENDIDO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_coach),
  UNIQUE KEY uq_coach_user (id_user),
  CONSTRAINT fk_coach_user FOREIGN KEY (id_user) REFERENCES `user` (id_user),
  CONSTRAINT ck_coach_calif CHECK (calificacion_promedio BETWEEN 0 AND 5)
) ENGINE=InnoDB COMMENT='RF-003 registro de coaches';

CREATE TABLE clientes (
  id_cliente        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user           INT UNSIGNED NOT NULL,
  tipo_cliente      ENUM('INDIVIDUAL','INSTITUCIONAL') NOT NULL DEFAULT 'INDIVIDUAL',
  modalidad         ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL,
  tipo_documento    ENUM('CC','CE','TI','PASAPORTE','NIT','OTRO') NOT NULL DEFAULT 'CC',
  documento         VARCHAR(30) NOT NULL,
  fecha_nacimiento  DATE NULL,
  genero            ENUM('FEMENINO','MASCULINO','OTRO','NO_INFORMA') NULL,
  ciudad            VARCHAR(80) NULL,
  peso_inicial_kg   DECIMAL(6,2) NULL,
  estatura_cm       DECIMAL(6,2) NULL,
  objetivo_principal VARCHAR(80) NULL,
  alergias          TEXT NULL,
  condiciones_medicas TEXT NULL,
  lesiones          TEXT NULL,
  medicamentos      TEXT NULL,
  contacto_emergencia_nombre VARCHAR(120) NULL,
  contacto_emergencia_telefono VARCHAR(30) NULL,
  id_institucion    INT UNSIGNED NULL,
  id_coach_asignado INT UNSIGNED NULL,
  estado_cliente    ENUM('PROSPECTO','PENDIENTE_PAGO','ACTIVO','INACTIVO','SUSPENDIDO','VENCIDO') NOT NULL DEFAULT 'PROSPECTO',
  fecha_activacion  DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_cliente),
  UNIQUE KEY uq_cliente_user (id_user),
  UNIQUE KEY uq_cliente_documento (tipo_documento, documento),
  KEY idx_cliente_estado (estado_cliente),
  KEY idx_cliente_modalidad (modalidad),
  KEY idx_cliente_inst (id_institucion),
  KEY idx_cliente_coach (id_coach_asignado),
  CONSTRAINT fk_cli_user FOREIGN KEY (id_user) REFERENCES `user` (id_user),
  CONSTRAINT fk_cli_inst FOREIGN KEY (id_institucion) REFERENCES instituciones (id_institucion),
  CONSTRAINT fk_cli_coach FOREIGN KEY (id_coach_asignado) REFERENCES coaches (id_coach)
) ENGINE=InnoDB COMMENT='Cliente individual o institucional con trazabilidad propia';

CREATE TABLE preferencias_cliente (
  id_preferencia    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  notif_email       TINYINT(1) NOT NULL DEFAULT 1,
  notif_push        TINYINT(1) NOT NULL DEFAULT 1,
  notif_whatsapp    TINYINT(1) NOT NULL DEFAULT 1,
  horario_preferido ENUM('MANANA','TARDE','NOCHE','INDIFERENTE') NULL,
  recordatorio_minutos SMALLINT UNSIGNED NOT NULL DEFAULT 60,
  idioma            VARCHAR(10) NOT NULL DEFAULT 'es',
  privacidad_fotos  ENUM('COACH','COACH_ADMIN','PRIVADO') NOT NULL DEFAULT 'COACH_ADMIN',
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_preferencia),
  UNIQUE KEY uq_pref_cliente (id_cliente),
  CONSTRAINT fk_pref_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB;

CREATE TABLE historial_estados_cliente (
  id_historial      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  estado_anterior   VARCHAR(30) NULL,
  estado_nuevo      VARCHAR(30) NOT NULL,
  motivo            VARCHAR(255) NULL,
  id_user_responsable INT UNSIGNED NULL,
  fecha_cambio      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_historial),
  KEY idx_hist_cli (id_cliente, fecha_cambio),
  CONSTRAINT fk_hist_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_hist_user FOREIGN KEY (id_user_responsable) REFERENCES `user` (id_user)
) ENGINE=InnoDB COMMENT='Auditoría obligatoria al cambiar estado_cliente';

CREATE TABLE historial_asignacion_coach (
  id_asignacion     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_coach_anterior INT UNSIGNED NULL,
  id_coach_nuevo    INT UNSIGNED NOT NULL,
  id_admin          INT UNSIGNED NULL,
  motivo            VARCHAR(255) NULL,
  asignado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_asignacion),
  KEY idx_asig_cli (id_cliente),
  CONSTRAINT fk_asig_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_asig_ant FOREIGN KEY (id_coach_anterior) REFERENCES coaches (id_coach),
  CONSTRAINT fk_asig_nue FOREIGN KEY (id_coach_nuevo) REFERENCES coaches (id_coach),
  CONSTRAINT fk_asig_adm FOREIGN KEY (id_admin) REFERENCES `user` (id_user)
) ENGINE=InnoDB COMMENT='RF-009 trazabilidad de asignación de coach';

CREATE TABLE valoraciones_coach (
  id_valoracion     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_coach          INT UNSIGNED NOT NULL,
  id_cliente        INT UNSIGNED NOT NULL,
  calificacion      TINYINT UNSIGNED NOT NULL,
  comentario        TEXT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_valoracion),
  UNIQUE KEY uq_val_coach_cli (id_coach, id_cliente),
  CONSTRAINT fk_val_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach),
  CONSTRAINT fk_val_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT ck_val_calif CHECK (calificacion BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE medidas_antropometricas_iniciales (
  id_medida_inicial INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  peso_kg           DECIMAL(6,2) NOT NULL,
  estatura_cm       DECIMAL(6,2) NOT NULL,
  cintura_cm        DECIMAL(6,2) NULL,
  cadera_cm         DECIMAL(6,2) NULL,
  brazo_cm          DECIMAL(6,2) NULL,
  pierna_cm         DECIMAL(6,2) NULL,
  imc               DECIMAL(5,2) GENERATED ALWAYS AS (
                      CASE WHEN estatura_cm > 0 THEN ROUND(peso_kg / POW(estatura_cm/100, 2), 2) ELSE NULL END
                    ) VIRTUAL,
  origen            ENUM('REGISTRO_PRESENCIAL','ADMIN','MIGRACION') NOT NULL DEFAULT 'REGISTRO_PRESENCIAL',
  registrada_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_medida_inicial),
  UNIQUE KEY uq_medini_cli (id_cliente),
  CONSTRAINT fk_medini_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT ck_medini_peso CHECK (peso_kg > 0 AND peso_kg < 400),
  CONSTRAINT ck_medini_est CHECK (estatura_cm > 50 AND estatura_cm < 250)
) ENGINE=InnoDB COMMENT='RF-053 línea base de progreso presencial';

-- -----------------------------------------------------------------------------
-- SECCIÓN 3 — Planes comerciales, módulos, cupones y enlaces institucionales
-- -----------------------------------------------------------------------------

CREATE TABLE planes (
  id_plan           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo            VARCHAR(40) NOT NULL,
  nombre            VARCHAR(120) NOT NULL,
  descripcion       TEXT NULL,
  modalidad         ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL,
  precio            DECIMAL(12,2) NOT NULL,
  moneda            CHAR(3) NOT NULL DEFAULT 'COP',
  duracion_dias     INT UNSIGNED NOT NULL,
  cupos_totales     INT UNSIGNED NOT NULL DEFAULT 0,
  cupos_reservados  INT UNSIGNED NOT NULL DEFAULT 0,
  cupos_ocupados    INT UNSIGNED NOT NULL DEFAULT 0,
  inclusiones       TEXT NULL,
  beneficios        TEXT NULL,
  renovacion_permitida TINYINT(1) NOT NULL DEFAULT 1,
  estado            ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_plan),
  UNIQUE KEY uq_plan_codigo (codigo),
  KEY idx_plan_mod_est (modalidad, estado),
  CONSTRAINT ck_plan_precio CHECK (precio >= 0),
  CONSTRAINT ck_plan_cupos CHECK (cupos_reservados + cupos_ocupados <= cupos_totales)
) ENGINE=InnoDB COMMENT='RF-034 catálogo Welcome; cupos para vw_cupos_planes';

CREATE TABLE modulos_servicio (
  id_modulo         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_modulo     VARCHAR(40) NOT NULL,
  nombre_modulo     VARCHAR(80) NOT NULL,
  descripcion       VARCHAR(255) NULL,
  exclusivo_modalidad ENUM('VIRTUAL','PRESENCIAL','TODAS') NOT NULL DEFAULT 'TODAS',
  estado            ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (id_modulo),
  UNIQUE KEY uq_mod_codigo (codigo_modulo)
) ENGINE=InnoDB COMMENT='Cobertura funcional habilitable por plan';

CREATE TABLE plan_modulos (
  id_plan_modulo    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_plan           INT UNSIGNED NOT NULL,
  id_modulo         SMALLINT UNSIGNED NOT NULL,
  incluido          TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id_plan_modulo),
  UNIQUE KEY uq_plan_mod (id_plan, id_modulo),
  CONSTRAINT fk_pm_plan FOREIGN KEY (id_plan) REFERENCES planes (id_plan),
  CONSTRAINT fk_pm_mod FOREIGN KEY (id_modulo) REFERENCES modulos_servicio (id_modulo)
) ENGINE=InnoDB;

CREATE TABLE enlaces_registro_institucional (
  id_enlace         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_institucion    INT UNSIGNED NOT NULL,
  id_plan           INT UNSIGNED NOT NULL,
  token             VARCHAR(80) NOT NULL,
  cupos_maximos     INT UNSIGNED NOT NULL,
  usos_actuales     INT UNSIGNED NOT NULL DEFAULT 0,
  fecha_expiracion  DATETIME NOT NULL,
  estado            ENUM('ACTIVO','INACTIVO','EXPIRADO','AGOTADO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_enlace),
  UNIQUE KEY uq_enlace_token (token),
  CONSTRAINT fk_enl_inst FOREIGN KEY (id_institucion) REFERENCES instituciones (id_institucion),
  CONSTRAINT fk_enl_plan FOREIGN KEY (id_plan) REFERENCES planes (id_plan)
) ENGINE=InnoDB COMMENT='RF-055 token único de registro institucional';

CREATE TABLE descuentos_cupones (
  id_cupon          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo            VARCHAR(40) NOT NULL,
  tipo              ENUM('PORCENTAJE','MONTO') NOT NULL,
  valor             DECIMAL(12,2) NOT NULL,
  id_plan           INT UNSIGNED NULL,
  fecha_inicio      DATETIME NOT NULL,
  fecha_fin         DATETIME NOT NULL,
  usos_maximos      INT UNSIGNED NULL,
  usos_actuales     INT UNSIGNED NOT NULL DEFAULT 0,
  estado            ENUM('ACTIVO','INACTIVO','EXPIRADO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_cupon),
  UNIQUE KEY uq_cupon_codigo (codigo),
  CONSTRAINT fk_cup_plan FOREIGN KEY (id_plan) REFERENCES planes (id_plan),
  CONSTRAINT ck_cup_valor CHECK (valor >= 0)
) ENGINE=InnoDB;

CREATE TABLE usos_cupones (
  id_uso_cupon      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cupon          INT UNSIGNED NOT NULL,
  id_solicitud      INT UNSIGNED NULL,
  correo            VARCHAR(150) NULL,
  usado_en          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_uso_cupon),
  KEY idx_uso_cup (id_cupon),
  CONSTRAINT fk_uso_cup FOREIGN KEY (id_cupon) REFERENCES descuentos_cupones (id_cupon)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- SECCIÓN 4 — Solicitudes, pagos, facturas, suscripciones y accesos
-- FKs circulares se agregan al final con ALTER TABLE
-- -----------------------------------------------------------------------------

CREATE TABLE solicitudes_compra (
  id_solicitud      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_plan           INT UNSIGNED NOT NULL,
  id_user           INT UNSIGNED NULL,
  id_cliente        INT UNSIGNED NULL,
  id_enlace         INT UNSIGNED NULL,
  id_cupon          INT UNSIGNED NULL,
  origen            ENUM('PASARELA','COMPROBANTE','ADMIN','INSTITUCIONAL','RENOVACION') NOT NULL,
  modalidad         ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL,
  nombres           VARCHAR(80) NOT NULL,
  apellidos         VARCHAR(80) NOT NULL,
  tipo_documento    VARCHAR(20) NOT NULL DEFAULT 'CC',
  documento         VARCHAR(30) NOT NULL,
  correo            VARCHAR(150) NOT NULL,
  telefono          VARCHAR(30) NULL,
  ciudad            VARCHAR(80) NULL,
  precio_lista      DECIMAL(12,2) NOT NULL,
  descuento         DECIMAL(12,2) NOT NULL DEFAULT 0,
  total             DECIMAL(12,2) NOT NULL,
  estado_solicitud  ENUM('PENDIENTE','VALIDADA','RECHAZADA','CANCELADA','EXPIRADA') NOT NULL DEFAULT 'PENDIENTE',
  reserva_cupo      TINYINT(1) NOT NULL DEFAULT 1,
  reserva_hasta     DATETIME NULL,
  observacion_admin TEXT NULL,
  id_admin_revisor  INT UNSIGNED NULL,
  revisada_en       DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_solicitud),
  KEY idx_sol_estado (estado_solicitud),
  KEY idx_sol_plan (id_plan),
  KEY idx_sol_correo (correo),
  CONSTRAINT fk_sol_plan FOREIGN KEY (id_plan) REFERENCES planes (id_plan),
  CONSTRAINT fk_sol_user FOREIGN KEY (id_user) REFERENCES `user` (id_user),
  CONSTRAINT fk_sol_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_sol_enl FOREIGN KEY (id_enlace) REFERENCES enlaces_registro_institucional (id_enlace),
  CONSTRAINT fk_sol_cup FOREIGN KEY (id_cupon) REFERENCES descuentos_cupones (id_cupon),
  CONSTRAINT fk_sol_adm FOREIGN KEY (id_admin_revisor) REFERENCES `user` (id_user)
) ENGINE=InnoDB COMMENT='RF-001/028/029 PENDIENTE y VALIDADA reservan cupo';

CREATE TABLE planes_cliente (
  id_plan_cliente   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_plan           INT UNSIGNED NOT NULL,
  id_solicitud      INT UNSIGNED NULL,
  id_pago           INT UNSIGNED NULL,
  id_suscripcion    INT UNSIGNED NULL,
  fecha_inicio      DATE NOT NULL,
  fecha_fin         DATE NOT NULL,
  estado            ENUM('PENDIENTE','ACTIVO','VENCIDO','CANCELADO','SUSPENDIDO') NOT NULL DEFAULT 'PENDIENTE',
  renovacion_automatica TINYINT(1) NOT NULL DEFAULT 0,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_plan_cliente),
  KEY idx_pc_cli_est (id_cliente, estado),
  KEY idx_pc_fechas (fecha_fin, estado),
  CONSTRAINT fk_pc_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_pc_plan FOREIGN KEY (id_plan) REFERENCES planes (id_plan),
  CONSTRAINT fk_pc_sol FOREIGN KEY (id_solicitud) REFERENCES solicitudes_compra (id_solicitud)
) ENGINE=InnoDB COMMENT='RF-030/033 vigencia del servicio adquirido';

CREATE TABLE pagos (
  id_pago           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_solicitud      INT UNSIGNED NULL,
  id_plan_cliente   INT UNSIGNED NULL,
  id_cliente        INT UNSIGNED NULL,
  tipo_pago         ENUM('PLAN','TIENDA','RENOVACION','SUSCRIPCION') NOT NULL DEFAULT 'PLAN',
  monto             DECIMAL(12,2) NOT NULL,
  moneda            CHAR(3) NOT NULL DEFAULT 'COP',
  metodo            ENUM('PASARELA','TRANSFERENCIA','NEQUI_MANUAL','DAVIPLATA_MANUAL','EFECTIVO','TARJETA') NOT NULL,
  pasarela          ENUM('WOMPI','PAYU','MERCADOPAGO','NEQUI','DAVIPLATA','NINGUNA') NOT NULL DEFAULT 'NINGUNA',
  referencia        VARCHAR(80) NULL,
  comprobante_url   VARCHAR(500) NULL,
  estado_pago       ENUM('PENDIENTE','VALIDADO','RECHAZADO','ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  fecha_pago        DATETIME NULL,
  fecha_validacion  DATETIME NULL,
  id_admin_validador INT UNSIGNED NULL,
  observacion       TEXT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_pago),
  UNIQUE KEY uq_pago_ref (referencia),
  KEY idx_pago_estado (estado_pago),
  KEY idx_pago_metodo (metodo, pasarela),
  CONSTRAINT fk_pag_sol FOREIGN KEY (id_solicitud) REFERENCES solicitudes_compra (id_solicitud),
  CONSTRAINT fk_pag_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_pag_adm FOREIGN KEY (id_admin_validador) REFERENCES `user` (id_user),
  CONSTRAINT ck_pag_monto CHECK (monto >= 0)
) ENGINE=InnoDB COMMENT='RF-028/029 VALIDADO activa; RECHAZADO libera cupo';

CREATE TABLE facturas (
  id_factura        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  numero_factura    VARCHAR(40) NOT NULL,
  id_pago           INT UNSIGNED NOT NULL,
  tipo              ENUM('PLAN','TIENDA') NOT NULL DEFAULT 'PLAN',
  razon_social      VARCHAR(150) NULL,
  documento_cliente VARCHAR(30) NULL,
  correo_cliente    VARCHAR(150) NULL,
  subtotal          DECIMAL(12,2) NOT NULL,
  impuestos         DECIMAL(12,2) NOT NULL DEFAULT 0,
  total             DECIMAL(12,2) NOT NULL,
  pdf_url           VARCHAR(500) NULL,
  xml_url           VARCHAR(500) NULL,
  estado            ENUM('EMITIDA','ANULADA') NOT NULL DEFAULT 'EMITIDA',
  emitida_en        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_factura),
  UNIQUE KEY uq_fac_numero (numero_factura),
  KEY idx_fac_pago (id_pago),
  CONSTRAINT fk_fac_pago FOREIGN KEY (id_pago) REFERENCES pagos (id_pago)
) ENGINE=InnoDB COMMENT='RF-032 facturación electrónica PDF/XML';

CREATE TABLE suscripciones_recurrentes (
  id_suscripcion    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_plan           INT UNSIGNED NOT NULL,
  id_plan_cliente   INT UNSIGNED NULL,
  periodicidad      ENUM('MENSUAL','TRIMESTRAL','SEMESTRAL','ANUAL') NOT NULL,
  token_pasarela    VARCHAR(120) NULL,
  pasarela          VARCHAR(30) NULL,
  estado            ENUM('ACTIVA','PAUSADA','CANCELADA','VENCIDA') NOT NULL DEFAULT 'ACTIVA',
  proximo_cobro     DATE NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_suscripcion),
  KEY idx_sus_cli (id_cliente, estado),
  CONSTRAINT fk_sus_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_sus_plan FOREIGN KEY (id_plan) REFERENCES planes (id_plan)
) ENGINE=InnoDB;

CREATE TABLE acceso_cliente_modulo (
  id_acceso         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_modulo         SMALLINT UNSIGNED NOT NULL,
  id_plan_cliente   INT UNSIGNED NOT NULL,
  estado            ENUM('ACTIVO','BLOQUEADO','VENCIDO') NOT NULL DEFAULT 'ACTIVO',
  fecha_inicio      DATE NOT NULL,
  fecha_fin         DATE NOT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_acceso),
  UNIQUE KEY uq_acc_cli_mod_pc (id_cliente, id_modulo, id_plan_cliente),
  KEY idx_acc_est (id_cliente, estado, fecha_fin),
  CONSTRAINT fk_acc_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_acc_mod FOREIGN KEY (id_modulo) REFERENCES modulos_servicio (id_modulo),
  CONSTRAINT fk_acc_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente (id_plan_cliente)
) ENGINE=InnoDB COMMENT='RF-031/035 acceso solo si activo y vigente';

CREATE TABLE configuracion_pasarelas (
  id_config_pasarela SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo_pasarela   ENUM('WOMPI','PAYU','MERCADOPAGO','NEQUI','DAVIPLATA') NOT NULL,
  nombre            VARCHAR(80) NOT NULL,
  modo              ENUM('SANDBOX','PRODUCCION') NOT NULL DEFAULT 'SANDBOX',
  comercio_id       VARCHAR(120) NULL,
  api_key_cifrada   VARCHAR(500) NULL,
  api_secret_cifrada VARCHAR(500) NULL,
  webhook_secret_cifrado VARCHAR(500) NULL,
  url_checkout      VARCHAR(500) NULL,
  url_webhook       VARCHAR(500) NULL,
  activa            TINYINT(1) NOT NULL DEFAULT 1,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_config_pasarela),
  UNIQUE KEY uq_pasarela (codigo_pasarela)
) ENGINE=InnoDB COMMENT='RF-050 credenciales y settings de pasarelas';

CREATE TABLE transacciones_pasarela (
  id_transaccion    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_pago           INT UNSIGNED NULL,
  id_solicitud      INT UNSIGNED NULL,
  id_orden          INT UNSIGNED NULL,
  pasarela          ENUM('WOMPI','PAYU','MERCADOPAGO','NEQUI','DAVIPLATA') NOT NULL,
  referencia_interna VARCHAR(80) NOT NULL,
  referencia_externa VARCHAR(120) NULL,
  monto             DECIMAL(12,2) NOT NULL,
  moneda            CHAR(3) NOT NULL DEFAULT 'COP',
  estado_pasarela   ENUM('CREATED','PENDING','APPROVED','DECLINED','CANCELLED','EXPIRED','ERROR') NOT NULL DEFAULT 'CREATED',
  firma_valida      TINYINT(1) NULL,
  idempotency_key   VARCHAR(80) NOT NULL,
  payload_envio     JSON NULL,
  webhook_data      JSON NULL,
  intentos_activacion TINYINT UNSIGNED NOT NULL DEFAULT 0,
  activacion_ok     TINYINT(1) NOT NULL DEFAULT 0,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizada_en    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_transaccion),
  UNIQUE KEY uq_trx_idem (idempotency_key),
  UNIQUE KEY uq_trx_refint (referencia_interna),
  KEY idx_trx_ext (referencia_externa),
  KEY idx_trx_est (estado_pasarela),
  CONSTRAINT fk_trx_pago FOREIGN KEY (id_pago) REFERENCES pagos (id_pago),
  CONSTRAINT fk_trx_sol FOREIGN KEY (id_solicitud) REFERENCES solicitudes_compra (id_solicitud)
) ENGINE=InnoDB COMMENT='RF-030/050 webhook, firma e idempotencia';

CREATE TABLE eventos_webhook (
  id_evento_webhook BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_transaccion    BIGINT UNSIGNED NULL,
  pasarela          VARCHAR(30) NOT NULL,
  event_id_externo  VARCHAR(120) NULL,
  headers_json      JSON NULL,
  payload_json      JSON NOT NULL,
  firma_recibida    VARCHAR(255) NULL,
  firma_valida      TINYINT(1) NOT NULL DEFAULT 0,
  procesado         TINYINT(1) NOT NULL DEFAULT 0,
  error_proceso     VARCHAR(500) NULL,
  recibido_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_evento_webhook),
  UNIQUE KEY uq_wh_ext (pasarela, event_id_externo),
  KEY idx_wh_trx (id_transaccion),
  CONSTRAINT fk_wh_trx FOREIGN KEY (id_transaccion) REFERENCES transacciones_pasarela (id_transaccion)
) ENGINE=InnoDB COMMENT='Trazabilidad de webhooks; evita duplicados';

CREATE TABLE conciliaciones_pasarela (
  id_conciliacion   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_transaccion    BIGINT UNSIGNED NOT NULL,
  estado_consultado VARCHAR(40) NULL,
  coincidencia      TINYINT(1) NULL,
  respuesta_json    JSON NULL,
  ejecutada_en      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_conciliacion),
  KEY idx_conc_trx (id_transaccion),
  CONSTRAINT fk_conc_trx FOREIGN KEY (id_transaccion) REFERENCES transacciones_pasarela (id_transaccion)
) ENGINE=InnoDB COMMENT='Job de reconciliación cada 5 minutos si no llega webhook';

CREATE TABLE intentos_activacion (
  id_intento        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_transaccion    BIGINT UNSIGNED NOT NULL,
  nro_intento       TINYINT UNSIGNED NOT NULL,
  resultado         ENUM('OK','ERROR','DUPLICADO_EVITADO') NOT NULL,
  detalle           VARCHAR(500) NULL,
  ejecutado_en      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_intento),
  KEY idx_int_trx (id_transaccion),
  CONSTRAINT fk_int_trx FOREIGN KEY (id_transaccion) REFERENCES transacciones_pasarela (id_transaccion)
) ENGINE=InnoDB COMMENT='Reintentos con backoff sin duplicar registros';

-- -----------------------------------------------------------------------------
-- SECCIÓN 5 — Programas virtuales y videos (RF-013, RF-041, validación IA)
-- -----------------------------------------------------------------------------

CREATE TABLE programas_virtuales (
  id_programa       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(120) NOT NULL,
  descripcion       TEXT NULL,
  id_plan           INT UNSIGNED NULL,
  estado            ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_programa),
  CONSTRAINT fk_prog_plan FOREIGN KEY (id_plan) REFERENCES planes (id_plan)
) ENGINE=InnoDB;

CREATE TABLE categorias_video (
  id_categoria_video INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_programa       INT UNSIGNED NOT NULL,
  nombre            VARCHAR(80) NOT NULL,
  orden             SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (id_categoria_video),
  CONSTRAINT fk_catvid_prog FOREIGN KEY (id_programa) REFERENCES programas_virtuales (id_programa)
) ENGINE=InnoDB;

CREATE TABLE videos (
  id_video          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_categoria_video INT UNSIGNED NOT NULL,
  dia_orden         SMALLINT UNSIGNED NOT NULL,
  titulo            VARCHAR(150) NOT NULL,
  descripcion       TEXT NULL,
  url_video         VARCHAR(500) NOT NULL,
  duracion_segundos INT UNSIGNED NULL,
  requiere_ia       TINYINT(1) NOT NULL DEFAULT 1,
  estado            ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_video),
  UNIQUE KEY uq_vid_cat_dia (id_categoria_video, dia_orden),
  CONSTRAINT fk_vid_cat FOREIGN KEY (id_categoria_video) REFERENCES categorias_video (id_categoria_video)
) ENGINE=InnoDB COMMENT='Desbloqueo progresivo por día';

CREATE TABLE progreso_videos (
  id_progreso_video INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_video          INT UNSIGNED NOT NULL,
  id_plan_cliente   INT UNSIGNED NULL,
  estado            ENUM('BLOQUEADO','DESBLOQUEADO','EN_CURSO','COMPLETADO') NOT NULL DEFAULT 'BLOQUEADO',
  iniciado_en       DATETIME NULL,
  completado_en     DATETIME NULL,
  porcentaje        DECIMAL(5,2) NOT NULL DEFAULT 0,
  llama_racha       TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id_progreso_video),
  UNIQUE KEY uq_pvid_cli (id_cliente, id_video),
  CONSTRAINT fk_pvid_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_pvid_vid FOREIGN KEY (id_video) REFERENCES videos (id_video),
  CONSTRAINT fk_pvid_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente (id_plan_cliente)
) ENGINE=InnoDB;

CREATE TABLE validaciones_ia_video (
  id_validacion_ia  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_progreso_video INT UNSIGNED NOT NULL,
  id_cliente        INT UNSIGNED NOT NULL,
  repeticiones_detectadas INT UNSIGNED NOT NULL DEFAULT 0,
  repeticiones_correctas INT UNSIGNED NOT NULL DEFAULT 0,
  alertas_postura   INT UNSIGNED NOT NULL DEFAULT 0,
  resultado         ENUM('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
  detalle_json      JSON NULL,
  registrada_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_validacion_ia),
  KEY idx_via_prog (id_progreso_video),
  CONSTRAINT fk_via_prog FOREIGN KEY (id_progreso_video) REFERENCES progreso_videos (id_progreso_video),
  CONSTRAINT fk_via_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB COMMENT='Validación de movimiento y postura por IA';

-- -----------------------------------------------------------------------------
-- SECCIÓN 6 — Entrenamiento (RF-010 a RF-015)
-- -----------------------------------------------------------------------------

CREATE TABLE planes_entrenamiento (
  id_plan_entrenamiento INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NULL,
  id_coach          INT UNSIGNED NOT NULL,
  id_plan_cliente   INT UNSIGNED NULL,
  nombre            VARCHAR(120) NOT NULL,
  objetivo          VARCHAR(120) NULL,
  modalidad         ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL,
  estado            ENUM('BORRADOR','PENDIENTE_VALIDACION','ACTIVO','INACTIVO','RECHAZADO') NOT NULL DEFAULT 'BORRADOR',
  id_admin_validador INT UNSIGNED NULL,
  validado_en       DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_plan_entrenamiento),
  KEY idx_pent_cli (id_cliente),
  CONSTRAINT fk_pent_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_pent_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach),
  CONSTRAINT fk_pent_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente (id_plan_cliente),
  CONSTRAINT fk_pent_adm FOREIGN KEY (id_admin_validador) REFERENCES `user` (id_user)
) ENGINE=InnoDB COMMENT='Envío a admin para validación antes de notificar';

CREATE TABLE rutinas (
  id_rutina         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_plan_entrenamiento INT UNSIGNED NOT NULL,
  nombre            VARCHAR(120) NOT NULL,
  descripcion       TEXT NULL,
  dia_semana        TINYINT UNSIGNED NULL,
  orden             SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  duracion_min      SMALLINT UNSIGNED NULL,
  estado            ENUM('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_rutina),
  CONSTRAINT fk_rut_pent FOREIGN KEY (id_plan_entrenamiento) REFERENCES planes_entrenamiento (id_plan_entrenamiento),
  CONSTRAINT ck_rut_dia CHECK (dia_semana IS NULL OR dia_semana BETWEEN 1 AND 7)
) ENGINE=InnoDB;

CREATE TABLE ejercicios (
  id_ejercicio      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(120) NOT NULL,
  grupo_muscular    VARCHAR(80) NULL,
  descripcion       TEXT NULL,
  instrucciones     TEXT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_ejercicio)
) ENGINE=InnoDB;

CREATE TABLE rutina_ejercicios (
  id_rutina_ejercicio INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_rutina         INT UNSIGNED NOT NULL,
  id_ejercicio      INT UNSIGNED NOT NULL,
  series            SMALLINT UNSIGNED NULL,
  repeticiones      VARCHAR(40) NULL,
  descanso_seg      SMALLINT UNSIGNED NULL,
  peso_sugerido_kg  DECIMAL(6,2) NULL,
  rir               TINYINT UNSIGNED NULL,
  ritmo_ejecucion   VARCHAR(40) NULL,
  orden             SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  notas             VARCHAR(255) NULL,
  PRIMARY KEY (id_rutina_ejercicio),
  KEY idx_re_rut (id_rutina),
  CONSTRAINT fk_re_rut FOREIGN KEY (id_rutina) REFERENCES rutinas (id_rutina),
  CONSTRAINT fk_re_ej FOREIGN KEY (id_ejercicio) REFERENCES ejercicios (id_ejercicio)
) ENGINE=InnoDB;

CREATE TABLE material_entrenamiento (
  id_material       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_ejercicio      INT UNSIGNED NULL,
  id_rutina         INT UNSIGNED NULL,
  tipo              ENUM('TEXTO','IMAGEN','VIDEO','PDF','ENLACE') NOT NULL,
  titulo            VARCHAR(150) NULL,
  url_archivo       VARCHAR(500) NULL,
  contenido_texto   TEXT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_material),
  CONSTRAINT fk_mat_ej FOREIGN KEY (id_ejercicio) REFERENCES ejercicios (id_ejercicio),
  CONSTRAINT fk_mat_rut FOREIGN KEY (id_rutina) REFERENCES rutinas (id_rutina)
) ENGINE=InnoDB COMMENT='RF-012 material de apoyo';

CREATE TABLE registro_rutinas (
  id_registro_rutina INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_rutina         INT UNSIGNED NOT NULL,
  fecha_ejecucion   DATE NOT NULL,
  completada        TINYINT(1) NOT NULL DEFAULT 1,
  dificultad        TINYINT UNSIGNED NULL,
  observacion_cliente TEXT NULL,
  registrada_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_registro_rutina),
  KEY idx_rr_cli (id_cliente, fecha_ejecucion),
  CONSTRAINT fk_rr_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_rr_rut FOREIGN KEY (id_rutina) REFERENCES rutinas (id_rutina),
  CONSTRAINT ck_rr_dif CHECK (dificultad IS NULL OR dificultad BETWEEN 1 AND 5)
) ENGINE=InnoDB COMMENT='RF-014 rutinas completadas';

CREATE TABLE logs_ejercicio_detalle (
  id_log            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_registro_rutina INT UNSIGNED NOT NULL,
  id_rutina_ejercicio INT UNSIGNED NOT NULL,
  series_realizadas SMALLINT UNSIGNED NULL,
  repeticiones_realizadas VARCHAR(40) NULL,
  peso_kg           DECIMAL(6,2) NULL,
  rir_real          TINYINT UNSIGNED NULL,
  observacion       VARCHAR(255) NULL,
  PRIMARY KEY (id_log),
  CONSTRAINT fk_log_rr FOREIGN KEY (id_registro_rutina) REFERENCES registro_rutinas (id_registro_rutina),
  CONSTRAINT fk_log_re FOREIGN KEY (id_rutina_ejercicio) REFERENCES rutina_ejercicios (id_rutina_ejercicio)
) ENGINE=InnoDB;

CREATE TABLE asistencias (
  id_asistencia     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_coach          INT UNSIGNED NOT NULL,
  id_sesion         INT UNSIGNED NULL,
  fecha             DATE NOT NULL,
  estado            ENUM('ASISTIO','INASISTENCIA','JUSTIFICADA') NOT NULL,
  llama_racha       TINYINT(1) NOT NULL DEFAULT 0,
  observacion       VARCHAR(255) NULL,
  registrada_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_asistencia),
  UNIQUE KEY uq_asist_cli_fecha (id_cliente, fecha, id_sesion),
  CONSTRAINT fk_asist_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_asist_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach)
) ENGINE=InnoDB COMMENT='Asistencia presencial; refleja llama en calendario';

CREATE TABLE rachas_cliente (
  id_racha          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  tipo              ENUM('VIDEO','ASISTENCIA','RUTINA') NOT NULL,
  dias_consecutivos INT UNSIGNED NOT NULL DEFAULT 0,
  mejor_racha       INT UNSIGNED NOT NULL DEFAULT 0,
  ultima_fecha      DATE NULL,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_racha),
  UNIQUE KEY uq_racha (id_cliente, tipo),
  CONSTRAINT fk_racha_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB COMMENT='Sistema de llamas tipo Duolingo/TikTok';

-- -----------------------------------------------------------------------------
-- SECCIÓN 7 — Nutrición e IA (RF-017 a RF-021)
-- -----------------------------------------------------------------------------

CREATE TABLE planes_nutricionales (
  id_plan_nutricional INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NULL,
  id_coach          INT UNSIGNED NOT NULL,
  id_plan_cliente   INT UNSIGNED NULL,
  nombre            VARCHAR(120) NOT NULL,
  objetivo          VARCHAR(120) NULL,
  kcal_objetivo     INT UNSIGNED NULL,
  proteinas_g       DECIMAL(8,2) NULL,
  carbohidratos_g   DECIMAL(8,2) NULL,
  grasas_g          DECIMAL(8,2) NULL,
  recomendaciones   TEXT NULL,
  estado            ENUM('BORRADOR','ACTIVO','INACTIVO') NOT NULL DEFAULT 'BORRADOR',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_plan_nutricional),
  CONSTRAINT fk_pnut_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_pnut_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach),
  CONSTRAINT fk_pnut_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente (id_plan_cliente)
) ENGINE=InnoDB;

CREATE TABLE comidas (
  id_comida         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_plan_nutricional INT UNSIGNED NOT NULL,
  tipo_comida       ENUM('DESAYUNO','MEDIA_MANANA','ALMUERZO','MERIENDA','CENA','SNACK') NOT NULL,
  nombre            VARCHAR(150) NOT NULL,
  receta            TEXT NULL,
  porcion           VARCHAR(80) NULL,
  calorias          DECIMAL(8,2) NULL,
  proteinas_g       DECIMAL(8,2) NULL,
  carbohidratos_g   DECIMAL(8,2) NULL,
  grasas_g          DECIMAL(8,2) NULL,
  fibra_g           DECIMAL(8,2) NULL,
  azucar_g          DECIMAL(8,2) NULL,
  sodio_mg          DECIMAL(8,2) NULL,
  colesterol_mg     DECIMAL(8,2) NULL,
  alergenos         VARCHAR(255) NULL,
  vitaminas_minerales TEXT NULL,
  orden             SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (id_comida),
  KEY idx_com_plan (id_plan_nutricional),
  CONSTRAINT fk_com_pnut FOREIGN KEY (id_plan_nutricional) REFERENCES planes_nutricionales (id_plan_nutricional)
) ENGINE=InnoDB;

CREATE TABLE consultas_ia_nutricion (
  id_consulta_ia    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_plan_nutricional INT UNSIGNED NULL,
  tipo              ENUM('VALIDAR_PLATO','SUGERIR_ALTERNATIVA') NOT NULL,
  foto_url          VARCHAR(500) NOT NULL,
  alimentos_detectados JSON NULL,
  cumple_estructura TINYINT(1) NULL,
  alerta            ENUM('VERDE','ROJA','AMARILLA') NULL,
  macros_estimados  JSON NULL,
  alternativas_json JSON NULL,
  respuesta_ia      TEXT NULL,
  registrada_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_consulta_ia),
  KEY idx_ianut_cli (id_cliente, registrada_en),
  CONSTRAINT fk_ianut_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_ianut_plan FOREIGN KEY (id_plan_nutricional) REFERENCES planes_nutricionales (id_plan_nutricional)
) ENGINE=InnoDB COMMENT='RF-021 historial de consultas IA para el coach';

-- -----------------------------------------------------------------------------
-- SECCIÓN 8 — Progreso, metas y recordatorios (RF-022 a RF-027, RF-042)
-- -----------------------------------------------------------------------------

CREATE TABLE registros_progreso (
  id_registro_progreso INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_coach          INT UNSIGNED NULL,
  id_plan_cliente   INT UNSIGNED NULL,
  fecha_registro    DATE NOT NULL,
  peso_kg           DECIMAL(6,2) NULL,
  estatura_cm       DECIMAL(6,2) NULL,
  porcentaje_grasa  DECIMAL(5,2) NULL,
  masa_muscular_kg  DECIMAL(6,2) NULL,
  imc               DECIMAL(5,2) NULL,
  cuello_cm         DECIMAL(6,2) NULL,
  hombro_cm         DECIMAL(6,2) NULL,
  pecho_cm          DECIMAL(6,2) NULL,
  brazo_cm          DECIMAL(6,2) NULL,
  antebrazo_cm      DECIMAL(6,2) NULL,
  cintura_cm        DECIMAL(6,2) NULL,
  abdomen_cm        DECIMAL(6,2) NULL,
  cadera_cm         DECIMAL(6,2) NULL,
  gluteo_cm         DECIMAL(6,2) NULL,
  pierna_cm         DECIMAL(6,2) NULL,
  gemelo_cm         DECIMAL(6,2) NULL,
  fc_reposo         SMALLINT UNSIGNED NULL,
  pa_sistolica      SMALLINT UNSIGNED NULL,
  pa_diastolica     SMALLINT UNSIGNED NULL,
  nivel_energia     TINYINT UNSIGNED NULL,
  calidad_sueno     TINYINT UNSIGNED NULL,
  nivel_estres      TINYINT UNSIGNED NULL,
  agua_litros       DECIMAL(4,2) NULL,
  observacion_coach TEXT NULL,
  es_linea_base     TINYINT(1) NOT NULL DEFAULT 0,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_registro_progreso),
  KEY idx_prog_cli_fecha (id_cliente, fecha_registro),
  CONSTRAINT fk_prog_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_prog_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach),
  CONSTRAINT fk_prog_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente (id_plan_cliente)
) ENGINE=InnoDB COMMENT='RF-022 a RF-027; agrupable por plan (carpetas)';

CREATE TABLE fotos_progreso (
  id_foto           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_registro_progreso INT UNSIGNED NOT NULL,
  angulo            ENUM('FRENTE','PERFIL','ESPALDA','OTRO') NOT NULL,
  url_foto          VARCHAR(500) NOT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_foto),
  CONSTRAINT fk_foto_prog FOREIGN KEY (id_registro_progreso) REFERENCES registros_progreso (id_registro_progreso)
) ENGINE=InnoDB;

CREATE TABLE metas_cliente (
  id_meta           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  nombre            VARCHAR(120) NOT NULL,
  metrica           VARCHAR(60) NOT NULL,
  valor_inicial     DECIMAL(10,2) NOT NULL,
  valor_actual      DECIMAL(10,2) NOT NULL,
  valor_objetivo    DECIMAL(10,2) NOT NULL,
  unidad            VARCHAR(20) NULL,
  fecha_inicio      DATE NOT NULL,
  fecha_objetivo    DATE NULL,
  estado            ENUM('ACTIVA','LOGRADA','CANCELADA') NOT NULL DEFAULT 'ACTIVA',
  progreso_porcentaje DECIMAL(6,2) GENERATED ALWAYS AS (
    CASE
      WHEN valor_objetivo = valor_inicial THEN 100
      ELSE ROUND(((valor_actual - valor_inicial) / (valor_objetivo - valor_inicial)) * 100, 2)
    END
  ) VIRTUAL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_meta),
  KEY idx_meta_cli (id_cliente),
  CONSTRAINT fk_meta_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB COMMENT='Metas SMART con progreso calculado';

CREATE TABLE recordatorios (
  id_recordatorio   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NULL,
  id_coach          INT UNSIGNED NULL,
  tipo              ENUM('ENTRENAMIENTO','COMIDA','SESION','PAGO','HIDRATACION','SUENO','EVENTO','OTRO') NOT NULL,
  titulo            VARCHAR(150) NOT NULL,
  mensaje           VARCHAR(500) NULL,
  recurrencia       ENUM('UNICO','DIARIO','SEMANAL','MENSUAL','DIAS_ESPECIFICOS') NOT NULL DEFAULT 'UNICO',
  dias_semana       VARCHAR(20) NULL,
  fecha_hora        DATETIME NOT NULL,
  minutos_antes     SMALLINT UNSIGNED NOT NULL DEFAULT 60,
  activo            TINYINT(1) NOT NULL DEFAULT 1,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_recordatorio),
  KEY idx_rec_cli (id_cliente, activo),
  CONSTRAINT fk_rec_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_rec_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach)
) ENGINE=InnoDB COMMENT='RF-042 recordatorios inteligentes';

-- -----------------------------------------------------------------------------
-- SECCIÓN 9 — Agenda, sesiones, eventos y satisfacción (RF-016, RF-040 a RF-045)
-- -----------------------------------------------------------------------------

CREATE TABLE disponibilidades (
  id_disponibilidad INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_coach          INT UNSIGNED NOT NULL,
  dia_semana        TINYINT UNSIGNED NOT NULL,
  hora_inicio       TIME NOT NULL,
  hora_fin          TIME NOT NULL,
  modalidad         ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL,
  activo            TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id_disponibilidad),
  KEY idx_disp_coach (id_coach, dia_semana),
  CONSTRAINT fk_disp_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach),
  CONSTRAINT ck_disp_dia CHECK (dia_semana BETWEEN 1 AND 7),
  CONSTRAINT ck_disp_horas CHECK (hora_fin > hora_inicio)
) ENGINE=InnoDB COMMENT='RF-045 disponibilidad del coach';

CREATE TABLE sesiones (
  id_sesion         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_coach          INT UNSIGNED NOT NULL,
  tipo              ENUM('INDIVIDUAL','GRUPAL','VIRTUAL','PRESENCIAL') NOT NULL,
  titulo            VARCHAR(150) NOT NULL,
  fecha             DATE NOT NULL,
  hora_inicio       TIME NOT NULL,
  hora_fin          TIME NOT NULL,
  modalidad         ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL,
  cupo_maximo       SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  cupo_reservado    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  estado            ENUM('PROGRAMADA','EN_CURSO','COMPLETADA','CANCELADA') NOT NULL DEFAULT 'PROGRAMADA',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_sesion),
  KEY idx_ses_coach_fecha (id_coach, fecha),
  CONSTRAINT fk_ses_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach)
) ENGINE=InnoDB COMMENT='RF-016 programación de entrenamientos';

CREATE TABLE sesion_participantes (
  id_sesion_participante INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_sesion         INT UNSIGNED NOT NULL,
  id_cliente        INT UNSIGNED NOT NULL,
  estado            ENUM('INSCRITO','CONFIRMADO','ASISTIO','AUSENTE','CANCELADO') NOT NULL DEFAULT 'INSCRITO',
  inscrito_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_sesion_participante),
  UNIQUE KEY uq_ses_cli (id_sesion, id_cliente),
  CONSTRAINT fk_sp_ses FOREIGN KEY (id_sesion) REFERENCES sesiones (id_sesion),
  CONSTRAINT fk_sp_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB;

CREATE TABLE eventos (
  id_evento         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_coach          INT UNSIGNED NULL,
  id_admin          INT UNSIGNED NULL,
  nombre            VARCHAR(150) NOT NULL,
  descripcion       TEXT NULL,
  tipo              ENUM('TALLER','RETO','CHARLA','EVENTO_ESPECIAL') NOT NULL DEFAULT 'TALLER',
  fecha_inicio      DATETIME NOT NULL,
  fecha_fin         DATETIME NULL,
  modalidad         ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL,
  cupo_maximo       INT UNSIGNED NULL,
  cupo_inscritos    INT UNSIGNED NOT NULL DEFAULT 0,
  estado            ENUM('BORRADOR','ACTIVO','CERRADO','CANCELADO') NOT NULL DEFAULT 'BORRADOR',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_evento),
  CONSTRAINT fk_ev_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach),
  CONSTRAINT fk_ev_adm FOREIGN KEY (id_admin) REFERENCES `user` (id_user)
) ENGINE=InnoDB COMMENT='RF-043 eventos y talleres';

CREATE TABLE evento_participantes (
  id_evento_participante INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_evento         INT UNSIGNED NOT NULL,
  id_cliente        INT UNSIGNED NOT NULL,
  estado            ENUM('INSCRITO','CONFIRMADO','ASISTIO','CANCELADO') NOT NULL DEFAULT 'INSCRITO',
  inscrito_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_evento_participante),
  UNIQUE KEY uq_ev_cli (id_evento, id_cliente),
  CONSTRAINT fk_ep_ev FOREIGN KEY (id_evento) REFERENCES eventos (id_evento),
  CONSTRAINT fk_ep_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB;

CREATE TABLE encuestas_satisfaccion (
  id_encuesta       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_plan_cliente   INT UNSIGNED NULL,
  calificacion      TINYINT UNSIGNED NOT NULL,
  comentario        TEXT NULL,
  recomienda_plan   TINYINT(1) NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_encuesta),
  KEY idx_enc_cli (id_cliente),
  CONSTRAINT fk_enc_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_enc_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente (id_plan_cliente),
  CONSTRAINT ck_enc_cal CHECK (calificacion BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- SECCIÓN 10 — Chat virtual, notificaciones y WhatsApp presencial
-- -----------------------------------------------------------------------------

CREATE TABLE chats (
  id_chat           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo              ENUM('INDIVIDUAL','COMUNIDAD') NOT NULL,
  id_plan           INT UNSIGNED NULL,
  id_coach          INT UNSIGNED NULL,
  nombre            VARCHAR(120) NULL,
  estado            ENUM('ACTIVO','ARCHIVADO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_chat),
  CONSTRAINT fk_chat_plan FOREIGN KEY (id_plan) REFERENCES planes (id_plan),
  CONSTRAINT fk_chat_coach FOREIGN KEY (id_coach) REFERENCES coaches (id_coach)
) ENGINE=InnoDB COMMENT='RF-036 chat interno solo modalidad Virtual';

CREATE TABLE chat_participantes (
  id_chat_participante INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_chat           INT UNSIGNED NOT NULL,
  id_user           INT UNSIGNED NOT NULL,
  rol_en_chat       ENUM('COACH','CLIENTE','ADMIN_SUPERVISOR') NOT NULL,
  unido_en          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_chat_participante),
  UNIQUE KEY uq_chat_user (id_chat, id_user),
  CONSTRAINT fk_cp_chat FOREIGN KEY (id_chat) REFERENCES chats (id_chat),
  CONSTRAINT fk_cp_user FOREIGN KEY (id_user) REFERENCES `user` (id_user)
) ENGINE=InnoDB;

CREATE TABLE mensajes (
  id_mensaje        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_chat           INT UNSIGNED NOT NULL,
  id_user_emisor    INT UNSIGNED NOT NULL,
  contenido         TEXT NOT NULL,
  tipo_mensaje      ENUM('TEXTO','IMAGEN','ARCHIVO','SISTEMA') NOT NULL DEFAULT 'TEXTO',
  url_adjunto       VARCHAR(500) NULL,
  leido             TINYINT(1) NOT NULL DEFAULT 0,
  enviado_en        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_mensaje),
  KEY idx_msg_chat (id_chat, enviado_en),
  CONSTRAINT fk_msg_chat FOREIGN KEY (id_chat) REFERENCES chats (id_chat),
  CONSTRAINT fk_msg_user FOREIGN KEY (id_user_emisor) REFERENCES `user` (id_user)
) ENGINE=InnoDB;

CREATE TABLE notificaciones (
  id_notificacion   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user           INT UNSIGNED NOT NULL,
  tipo              ENUM('PAGO','VENCIMIENTO','RUTINA','NUTRICION','AGENDA','EVENTO','MOTIVACIONAL','SISTEMA','WHATSAPP','TIENDA') NOT NULL,
  titulo            VARCHAR(150) NOT NULL,
  mensaje           VARCHAR(500) NOT NULL,
  leida             TINYINT(1) NOT NULL DEFAULT 0,
  canal             ENUM('IN_APP','EMAIL','PUSH','WHATSAPP') NOT NULL DEFAULT 'IN_APP',
  referencia_tipo   VARCHAR(40) NULL,
  referencia_id     BIGINT UNSIGNED NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_notificacion),
  KEY idx_not_user (id_user, leida, created_at),
  CONSTRAINT fk_not_user FOREIGN KEY (id_user) REFERENCES `user` (id_user)
) ENGINE=InnoDB COMMENT='RF-037 a RF-039';

CREATE TABLE configuracion_whatsapp (
  id_config_wa      SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  numero_business   VARCHAR(30) NOT NULL,
  api_token_cifrado VARCHAR(500) NULL,
  link_grupo_principal VARCHAR(300) NULL,
  mensaje_bienvenida TEXT NULL,
  horario_atencion  VARCHAR(120) NULL,
  respuestas_automaticas JSON NULL,
  activa            TINYINT(1) NOT NULL DEFAULT 1,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_config_wa)
) ENGINE=InnoDB COMMENT='RF-052 configuración WhatsApp Business';

CREATE TABLE grupos_whatsapp (
  id_grupo_wa       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_config_wa      SMALLINT UNSIGNED NOT NULL,
  nombre            VARCHAR(120) NOT NULL,
  tipo              ENUM('HORARIO','PLAN','MODALIDAD','GENERAL') NOT NULL,
  criterio          VARCHAR(80) NULL,
  link_invitacion   VARCHAR(300) NOT NULL,
  estado            ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_grupo_wa),
  CONSTRAINT fk_gwa_cfg FOREIGN KEY (id_config_wa) REFERENCES configuracion_whatsapp (id_config_wa)
) ENGINE=InnoDB;

CREATE TABLE participantes_grupo_whatsapp (
  id_part_wa        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_grupo_wa       INT UNSIGNED NOT NULL,
  id_cliente        INT UNSIGNED NOT NULL,
  estado            ENUM('INVITADO','UNIDO','ELIMINADO') NOT NULL DEFAULT 'INVITADO',
  unido_en          DATETIME NULL,
  PRIMARY KEY (id_part_wa),
  UNIQUE KEY uq_gwa_cli (id_grupo_wa, id_cliente),
  CONSTRAINT fk_pwa_g FOREIGN KEY (id_grupo_wa) REFERENCES grupos_whatsapp (id_grupo_wa),
  CONSTRAINT fk_pwa_c FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB;

CREATE TABLE invitaciones_whatsapp (
  id_invitacion_wa  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  id_grupo_wa       INT UNSIGNED NULL,
  link_mostrado     VARCHAR(300) NOT NULL,
  mostrada_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  aceptada_en       DATETIME NULL,
  cerrada_sin_aceptar TINYINT(1) NOT NULL DEFAULT 0,
  persistencia_local TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id_invitacion_wa),
  KEY idx_iwa_cli (id_cliente),
  CONSTRAINT fk_iwa_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_iwa_g FOREIGN KEY (id_grupo_wa) REFERENCES grupos_whatsapp (id_grupo_wa)
) ENGINE=InnoDB COMMENT='Alert flotante post-registro presencial';

-- -----------------------------------------------------------------------------
-- SECCIÓN 11 — Tienda e-commerce exclusiva presencial (RF-051)
-- -----------------------------------------------------------------------------

CREATE TABLE categorias_producto (
  id_categoria_producto INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(80) NOT NULL,
  descripcion       VARCHAR(255) NULL,
  estado            ENUM('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA',
  PRIMARY KEY (id_categoria_producto)
) ENGINE=InnoDB;

CREATE TABLE productos_tienda (
  id_producto       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_categoria_producto INT UNSIGNED NOT NULL,
  sku               VARCHAR(40) NOT NULL,
  nombre            VARCHAR(150) NOT NULL,
  descripcion       TEXT NULL,
  precio            DECIMAL(12,2) NOT NULL,
  imagen_url        VARCHAR(500) NULL,
  stock_total       INT NOT NULL DEFAULT 0,
  stock_minimo      INT NOT NULL DEFAULT 2,
  exclusivo_presencial TINYINT(1) NOT NULL DEFAULT 1,
  estado            ENUM('ACTIVO','INACTIVO','AGOTADO') NOT NULL DEFAULT 'ACTIVO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_producto),
  UNIQUE KEY uq_prod_sku (sku),
  KEY idx_prod_cat (id_categoria_producto),
  CONSTRAINT fk_prod_cat FOREIGN KEY (id_categoria_producto) REFERENCES categorias_producto (id_categoria_producto),
  CONSTRAINT ck_prod_precio CHECK (precio >= 0)
) ENGINE=InnoDB COMMENT='RF-051 solo clientes presenciales';

CREATE TABLE producto_variantes (
  id_variante       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_producto       INT UNSIGNED NOT NULL,
  talla             VARCHAR(20) NULL,
  color             VARCHAR(40) NULL,
  sku_variante      VARCHAR(50) NOT NULL,
  stock             INT NOT NULL DEFAULT 0,
  estado            ENUM('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA',
  PRIMARY KEY (id_variante),
  UNIQUE KEY uq_var_sku (sku_variante),
  UNIQUE KEY uq_var_prod (id_producto, talla, color),
  CONSTRAINT fk_var_prod FOREIGN KEY (id_producto) REFERENCES productos_tienda (id_producto)
) ENGINE=InnoDB COMMENT='Stock por talla y color';

CREATE TABLE carritos (
  id_carrito        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_cliente        INT UNSIGNED NOT NULL,
  estado            ENUM('ABIERTO','CONVERTIDO','ABANDONADO') NOT NULL DEFAULT 'ABIERTO',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_carrito),
  KEY idx_car_cli (id_cliente, estado),
  CONSTRAINT fk_car_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB;

CREATE TABLE carrito_items (
  id_item           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_carrito        INT UNSIGNED NOT NULL,
  id_producto       INT UNSIGNED NOT NULL,
  id_variante       INT UNSIGNED NULL,
  cantidad          INT UNSIGNED NOT NULL DEFAULT 1,
  precio_unitario   DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id_item),
  CONSTRAINT fk_ci_car FOREIGN KEY (id_carrito) REFERENCES carritos (id_carrito),
  CONSTRAINT fk_ci_prod FOREIGN KEY (id_producto) REFERENCES productos_tienda (id_producto),
  CONSTRAINT fk_ci_var FOREIGN KEY (id_variante) REFERENCES producto_variantes (id_variante),
  CONSTRAINT ck_ci_cant CHECK (cantidad > 0)
) ENGINE=InnoDB;

CREATE TABLE ordenes_tienda (
  id_orden          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  numero_orden      VARCHAR(40) NOT NULL,
  id_cliente        INT UNSIGNED NOT NULL,
  id_pago           INT UNSIGNED NULL,
  subtotal          DECIMAL(12,2) NOT NULL,
  costo_envio       DECIMAL(12,2) NOT NULL DEFAULT 0,
  total             DECIMAL(12,2) NOT NULL,
  tipo_entrega      ENUM('RECOGIDA_SEDE','ENVIO_LOCAL','ENVIO_NACIONAL') NOT NULL,
  direccion_entrega VARCHAR(255) NULL,
  ciudad_entrega    VARCHAR(80) NULL,
  estado_orden      ENUM('PENDIENTE_PAGO','PAGADA','PREPARADA','ENVIADA','ENTREGADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE_PAGO',
  observacion_admin VARCHAR(255) NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_orden),
  UNIQUE KEY uq_ord_num (numero_orden),
  KEY idx_ord_cli (id_cliente, estado_orden),
  CONSTRAINT fk_ord_cli FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
  CONSTRAINT fk_ord_pago FOREIGN KEY (id_pago) REFERENCES pagos (id_pago)
) ENGINE=InnoDB;

CREATE TABLE orden_detalle (
  id_detalle        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_orden          INT UNSIGNED NOT NULL,
  id_producto       INT UNSIGNED NOT NULL,
  id_variante       INT UNSIGNED NULL,
  cantidad          INT UNSIGNED NOT NULL,
  precio_unitario   DECIMAL(12,2) NOT NULL,
  subtotal          DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id_detalle),
  CONSTRAINT fk_od_ord FOREIGN KEY (id_orden) REFERENCES ordenes_tienda (id_orden),
  CONSTRAINT fk_od_prod FOREIGN KEY (id_producto) REFERENCES productos_tienda (id_producto),
  CONSTRAINT fk_od_var FOREIGN KEY (id_variante) REFERENCES producto_variantes (id_variante)
) ENGINE=InnoDB;

CREATE TABLE alertas_stock (
  id_alerta_stock   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_producto       INT UNSIGNED NOT NULL,
  id_variante       INT UNSIGNED NULL,
  stock_actual      INT NOT NULL,
  umbral            INT NOT NULL,
  notificada_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_alerta_stock),
  CONSTRAINT fk_as_prod FOREIGN KEY (id_producto) REFERENCES productos_tienda (id_producto),
  CONSTRAINT fk_as_var FOREIGN KEY (id_variante) REFERENCES producto_variantes (id_variante)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- SECCIÓN 12 — Reportes, auditoría y configuración (RF-006, RF-046 a RF-049)
-- -----------------------------------------------------------------------------

CREATE TABLE reportes_generados (
  id_reporte        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user           INT UNSIGNED NOT NULL,
  tipo              ENUM('PROGRESO','ASISTENCIA','PAGOS','ADMINISTRATIVO','ESTADISTICAS','VENTAS','OTRO') NOT NULL,
  parametros_json   JSON NULL,
  formato           ENUM('PDF','EXCEL') NOT NULL,
  url_archivo       VARCHAR(500) NULL,
  generado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_reporte),
  KEY idx_rep_user (id_user, generado_en),
  CONSTRAINT fk_rep_user FOREIGN KEY (id_user) REFERENCES `user` (id_user)
) ENGINE=InnoDB;

CREATE TABLE bitacora_sistema (
  id_bitacora       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user           INT UNSIGNED NULL,
  accion            VARCHAR(80) NOT NULL,
  entidad           VARCHAR(80) NOT NULL,
  id_entidad        VARCHAR(40) NULL,
  datos_antes       JSON NULL,
  datos_despues     JSON NULL,
  ip                VARCHAR(45) NULL,
  resultado         ENUM('OK','ERROR') NOT NULL DEFAULT 'OK',
  mensaje           VARCHAR(500) NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_bitacora),
  KEY idx_bit_ent (entidad, id_entidad),
  KEY idx_bit_user (id_user, created_at),
  KEY idx_bit_accion (accion, created_at),
  CONSTRAINT fk_bit_user FOREIGN KEY (id_user) REFERENCES `user` (id_user)
) ENGINE=InnoDB COMMENT='RNF-07 operaciones críticas auditables';

CREATE TABLE configuracion_sistema (
  id_config         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  clave             VARCHAR(80) NOT NULL,
  valor             TEXT NULL,
  descripcion       VARCHAR(255) NULL,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_config),
  UNIQUE KEY uq_cfg_clave (clave)
) ENGINE=InnoDB;

CREATE TABLE matriz_trazabilidad (
  id_trazabilidad   SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_rf             VARCHAR(10) NOT NULL,
  id_hu             VARCHAR(20) NOT NULL,
  id_cu             VARCHAR(10) NOT NULL,
  modulo            VARCHAR(60) NOT NULL,
  actor_principal   VARCHAR(80) NOT NULL,
  tablas_relacionadas VARCHAR(500) NOT NULL,
  PRIMARY KEY (id_trazabilidad),
  UNIQUE KEY uq_trz_rf (id_rf)
) ENGINE=InnoDB COMMENT='Línea base RF-HU-CU-tablas StayFit v4.0';

-- -----------------------------------------------------------------------------
-- FKs circulares: pagos <-> planes_cliente <-> suscripciones
-- -----------------------------------------------------------------------------

ALTER TABLE planes_cliente
  ADD CONSTRAINT fk_pc_pago FOREIGN KEY (id_pago) REFERENCES pagos (id_pago),
  ADD CONSTRAINT fk_pc_sus FOREIGN KEY (id_suscripcion) REFERENCES suscripciones_recurrentes (id_suscripcion);

ALTER TABLE pagos
  ADD CONSTRAINT fk_pag_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente (id_plan_cliente);

ALTER TABLE suscripciones_recurrentes
  ADD CONSTRAINT fk_sus_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente (id_plan_cliente);

ALTER TABLE transacciones_pasarela
  ADD CONSTRAINT fk_trx_ord FOREIGN KEY (id_orden) REFERENCES ordenes_tienda (id_orden);

ALTER TABLE usos_cupones
  ADD CONSTRAINT fk_uso_sol FOREIGN KEY (id_solicitud) REFERENCES solicitudes_compra (id_solicitud);

ALTER TABLE asistencias
  ADD CONSTRAINT fk_asist_ses FOREIGN KEY (id_sesion) REFERENCES sesiones (id_sesion);

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
-- VISTAS DE OPERACIÓN Y REPORTING (7 vistas)
-- -----------------------------------------------------------------------------

CREATE OR REPLACE VIEW vw_cupos_planes AS
SELECT
  p.id_plan,
  p.codigo,
  p.nombre,
  p.modalidad,
  p.estado,
  p.cupos_totales,
  p.cupos_reservados,
  p.cupos_ocupados,
  (p.cupos_totales - p.cupos_reservados - p.cupos_ocupados) AS cupos_disponibles,
  CASE
    WHEN p.estado = 'INACTIVO' THEN 'INACTIVO'
    WHEN (p.cupos_totales - p.cupos_reservados - p.cupos_ocupados) <= 0 THEN 'CUPO_LLENO'
    WHEN (p.cupos_totales - p.cupos_reservados - p.cupos_ocupados) <= GREATEST(ROUND(p.cupos_totales * 0.15), 1) THEN 'ULTIMOS_CUPOS'
    ELSE 'DISPONIBLE'
  END AS estado_cupo
FROM planes p;

CREATE OR REPLACE VIEW vw_clientes_activos AS
SELECT
  c.id_cliente,
  u.nombres,
  u.apellidos,
  u.correo,
  c.modalidad,
  c.tipo_cliente,
  c.estado_cliente,
  c.id_institucion,
  c.id_coach_asignado,
  pc.id_plan_cliente,
  pl.nombre AS plan_nombre,
  pc.fecha_inicio,
  pc.fecha_fin,
  pc.estado AS estado_plan
FROM clientes c
JOIN `user` u ON u.id_user = c.id_user
LEFT JOIN planes_cliente pc ON pc.id_cliente = c.id_cliente AND pc.estado = 'ACTIVO'
LEFT JOIN planes pl ON pl.id_plan = pc.id_plan;

CREATE OR REPLACE VIEW vw_pagos_por_estado AS
SELECT
  p.id_pago,
  p.tipo_pago,
  p.metodo,
  p.pasarela,
  p.estado_pago,
  p.monto,
  p.moneda,
  p.referencia,
  p.created_at,
  s.estado_solicitud,
  t.estado_pasarela,
  t.firma_valida
FROM pagos p
LEFT JOIN solicitudes_compra s ON s.id_solicitud = p.id_solicitud
LEFT JOIN transacciones_pasarela t ON t.id_pago = p.id_pago;

CREATE OR REPLACE VIEW vw_accesos_vigentes AS
SELECT
  a.id_acceso,
  a.id_cliente,
  m.codigo_modulo,
  m.nombre_modulo,
  a.estado,
  a.fecha_inicio,
  a.fecha_fin,
  pc.estado AS estado_plan,
  CASE
    WHEN a.estado = 'ACTIVO' AND pc.estado = 'ACTIVO' AND a.fecha_fin >= CURDATE() THEN 1
    ELSE 0
  END AS acceso_habilitado
FROM acceso_cliente_modulo a
JOIN modulos_servicio m ON m.id_modulo = a.id_modulo
JOIN planes_cliente pc ON pc.id_plan_cliente = a.id_plan_cliente;

CREATE OR REPLACE VIEW vw_progreso_resumen AS
SELECT
  r.id_cliente,
  r.id_plan_cliente,
  MIN(r.fecha_registro) AS primera_fecha,
  MAX(r.fecha_registro) AS ultima_fecha,
  COUNT(*) AS total_registros,
  MIN(r.peso_kg) AS peso_min,
  MAX(r.peso_kg) AS peso_max
FROM registros_progreso r
GROUP BY r.id_cliente, r.id_plan_cliente;

CREATE OR REPLACE VIEW vw_ingresos_pasarela AS
SELECT
  COALESCE(p.pasarela, 'NINGUNA') AS pasarela,
  p.metodo,
  p.estado_pago,
  DATE(p.fecha_pago) AS fecha,
  COUNT(*) AS cantidad,
  SUM(p.monto) AS total
FROM pagos p
GROUP BY COALESCE(p.pasarela, 'NINGUNA'), p.metodo, p.estado_pago, DATE(p.fecha_pago);

CREATE OR REPLACE VIEW vw_asistencia_racha AS
SELECT
  c.id_cliente,
  u.nombres,
  u.apellidos,
  rc.tipo,
  rc.dias_consecutivos,
  rc.mejor_racha,
  rc.ultima_fecha
FROM rachas_cliente rc
JOIN clientes c ON c.id_cliente = rc.id_cliente
JOIN `user` u ON u.id_user = c.id_user;

-- -----------------------------------------------------------------------------
-- TRIGGERS DE TRAZABILIDAD
-- -----------------------------------------------------------------------------

DELIMITER $$

CREATE TRIGGER trg_clientes_estado_ai
AFTER INSERT ON clientes
FOR EACH ROW
BEGIN
  INSERT INTO historial_estados_cliente (id_cliente, estado_anterior, estado_nuevo, motivo)
  VALUES (NEW.id_cliente, NULL, NEW.estado_cliente, 'ALTA_CLIENTE');
END$$

CREATE TRIGGER trg_clientes_estado_au
AFTER UPDATE ON clientes
FOR EACH ROW
BEGIN
  IF NOT (OLD.estado_cliente <=> NEW.estado_cliente) THEN
    INSERT INTO historial_estados_cliente (id_cliente, estado_anterior, estado_nuevo, motivo)
    VALUES (NEW.id_cliente, OLD.estado_cliente, NEW.estado_cliente, 'CAMBIO_ESTADO');
  END IF;
  IF NOT (OLD.id_coach_asignado <=> NEW.id_coach_asignado) AND NEW.id_coach_asignado IS NOT NULL THEN
    INSERT INTO historial_asignacion_coach (id_cliente, id_coach_anterior, id_coach_nuevo, motivo)
    VALUES (NEW.id_cliente, OLD.id_coach_asignado, NEW.id_coach_asignado, 'CAMBIO_COACH');
  END IF;
END$$

CREATE TRIGGER trg_valoracion_coach_ai
AFTER INSERT ON valoraciones_coach
FOR EACH ROW
BEGIN
  UPDATE coaches c
  SET total_valoraciones = (SELECT COUNT(*) FROM valoraciones_coach v WHERE v.id_coach = NEW.id_coach),
      calificacion_promedio = (SELECT AVG(v.calificacion) FROM valoraciones_coach v WHERE v.id_coach = NEW.id_coach)
  WHERE c.id_coach = NEW.id_coach;
END$$

DELIMITER ;

-- -----------------------------------------------------------------------------
-- DATOS SEMILLA
-- -----------------------------------------------------------------------------

INSERT INTO roles (codigo_rol, nombre_rol, descripcion) VALUES
('ADMIN', 'Administrador', 'Gestión total de la plataforma'),
('COACH', 'Coach', 'Acompañamiento de clientes asignados'),
('CLIENTE', 'Cliente individual', 'Cliente Virtual o Presencial'),
('CLIENTE_INSTITUCIONAL', 'Cliente institucional', 'Cliente vinculado a institución');

INSERT INTO modulos_servicio (codigo_modulo, nombre_modulo, exclusivo_modalidad) VALUES
('ENTRENAMIENTO', 'Entrenamiento', 'TODAS'),
('NUTRICION', 'Nutrición', 'TODAS'),
('VIDEOS', 'Contenido virtual', 'VIRTUAL'),
('PROGRESO', 'Progreso físico', 'TODAS'),
('AGENDA', 'Agenda y calendario', 'TODAS'),
('PAGOS', 'Pagos y facturación', 'TODAS'),
('COMUNICACION', 'Comunicación', 'TODAS'),
('TIENDA', 'Tienda FigueFit', 'PRESENCIAL');

INSERT INTO configuracion_pasarelas (codigo_pasarela, nombre, modo, url_webhook, activa) VALUES
('WOMPI', 'Wompi (Bancolombia)', 'SANDBOX', '/api/webhook/pasarela/wompi', 1),
('PAYU', 'PayU Latam', 'SANDBOX', '/api/webhook/pasarela/payu', 1),
('MERCADOPAGO', 'MercadoPago', 'SANDBOX', '/api/webhook/pasarela/mercadopago', 1),
('NEQUI', 'Nequi', 'SANDBOX', '/api/webhook/pasarela/nequi', 1),
('DAVIPLATA', 'Daviplata', 'SANDBOX', '/api/webhook/pasarela/daviplata', 1);

INSERT INTO categorias_producto (nombre, descripcion) VALUES
('Camisetas', 'Camisetas de la marca FigueFit'),
('Accesorios', 'Bandas, matas, botellas y toallas'),
('Suplementos', 'Proteínas, pre-entrenos y vitaminas'),
('Identificación de marca', 'Gorras, termos y mochilas');

INSERT INTO configuracion_sistema (clave, valor, descripcion) VALUES
('app_nombre', 'StayFit', 'Nombre comercial de la plataforma'),
('organizacion', 'FigueFit', 'Organización operadora'),
('moneda', 'COP', 'Moneda por defecto'),
('umbral_ultimos_cupos', '0.15', 'Porcentaje para estado ULTIMOS_CUPOS'),
('job_vencimiento_planes', 'diario', 'RF-033 planes fecha_fin < hoy a VENCIDO'),
('job_reconciliacion_pasarela', '5min', 'Consulta estado si no llega webhook'),
('tienda_solo_presencial', '1', 'RF-051 restricción de tienda'),
('chat_interno_solo_virtual', '1', 'RF-036 / RF-052 dualidad de comunicación');

INSERT INTO matriz_trazabilidad (id_rf, id_hu, id_cu, modulo, actor_principal, tablas_relacionadas) VALUES
('RF-001','HU-ACC-001','CU-01','Usuarios y acceso','Visitante','user, roles, user_roles, clientes, planes, solicitudes_compra'),
('RF-002','HU-ACC-002','CU-02','Usuarios y acceso','Administrador','user, roles, user_roles, clientes'),
('RF-003','HU-ACC-003','CU-03','Usuarios y acceso','Administrador','user, roles, user_roles, coaches'),
('RF-004','HU-ACC-004','CU-04','Usuarios y acceso','Usuario','user, roles, user_roles, sesiones_auth'),
('RF-005','HU-ACC-005','CU-05','Usuarios y acceso','Usuario','user, tokens_recuperacion'),
('RF-006','HU-ACC-006','CU-06','Usuarios y acceso','Administrador','user, roles, user_roles, coaches, clientes, bitacora_sistema'),
('RF-007','HU-PER-007','CU-07','Perfil y clientes','Cliente','user, clientes'),
('RF-008','HU-PER-008','CU-08','Perfil y clientes','Cliente','clientes, preferencias_cliente'),
('RF-009','HU-PER-009','CU-09','Perfil y clientes','Administrador','clientes, coaches, historial_asignacion_coach, bitacora_sistema'),
('RF-010','HU-ENT-010','CU-10','Entrenamiento','Coach','planes_entrenamiento, rutinas, ejercicios, rutina_ejercicios, material_entrenamiento'),
('RF-011','HU-ENT-011','CU-11','Entrenamiento','Coach','rutinas, planes_entrenamiento, planes_cliente'),
('RF-012','HU-ENT-012','CU-12','Entrenamiento','Coach','material_entrenamiento, ejercicios'),
('RF-013','HU-ENT-013','CU-13','Entrenamiento','Cliente','rutinas, ejercicios, rutina_ejercicios, material_entrenamiento, acceso_cliente_modulo'),
('RF-014','HU-ENT-014','CU-14','Entrenamiento','Cliente','registro_rutinas, rutinas, logs_ejercicio_detalle'),
('RF-015','HU-ENT-015','CU-15','Entrenamiento','Coach','rutinas, ejercicios, rutina_ejercicios, bitacora_sistema'),
('RF-016','HU-AGE-016','CU-16','Agenda y sesiones','Coach','sesiones, sesion_participantes, disponibilidades'),
('RF-017','HU-NUT-017','CU-17','Nutrición','Coach','planes_nutricionales, comidas, planes_cliente'),
('RF-018','HU-NUT-018','CU-18','Nutrición','Coach','planes_nutricionales, planes_cliente'),
('RF-019','HU-NUT-019','CU-19','Nutrición','Cliente','planes_nutricionales, comidas, acceso_cliente_modulo'),
('RF-020','HU-NUT-020','CU-20','Nutrición','Coach','planes_nutricionales, comidas, bitacora_sistema'),
('RF-021','HU-NUT-021','CU-21','Nutrición','Cliente / Sistema','consultas_ia_nutricion, planes_nutricionales, comidas'),
('RF-022','HU-PRO-022','CU-22','Progreso','Cliente','registros_progreso'),
('RF-023','HU-PRO-023','CU-23','Progreso','Cliente / Coach','registros_progreso'),
('RF-024','HU-PRO-024','CU-24','Progreso','Coach','registros_progreso, fotos_progreso'),
('RF-025','HU-PRO-025','CU-25','Progreso','Cliente / Coach','registros_progreso'),
('RF-026','HU-PRO-026','CU-26','Progreso','Cliente / Coach','registros_progreso, medidas_antropometricas_iniciales'),
('RF-027','HU-PRO-027','CU-27','Progreso','Coach','registros_progreso'),
('RF-028','HU-PAG-028','CU-28','Pagos y acceso','Visitante / Cliente','solicitudes_compra, pagos, facturas, transacciones_pasarela, planes'),
('RF-029','HU-PAG-029','CU-29','Pagos y acceso','Administrador','solicitudes_compra, pagos, bitacora_sistema, planes'),
('RF-030','HU-PAG-030','CU-30','Pagos y acceso','Sistema','transacciones_pasarela, pagos, solicitudes_compra, clientes, planes_cliente, acceso_cliente_modulo, facturas, intentos_activacion'),
('RF-031','HU-PAG-031','CU-31','Pagos y acceso','Sistema','acceso_cliente_modulo, planes_cliente, plan_modulos'),
('RF-032','HU-PAG-032','CU-32','Pagos y acceso','Cliente','pagos, facturas, planes_cliente'),
('RF-033','HU-PAG-033','CU-33','Pagos y acceso','Sistema','planes_cliente, acceso_cliente_modulo'),
('RF-034','HU-PAG-034','CU-34','Pagos y acceso','Administrador','planes, modulos_servicio, plan_modulos'),
('RF-035','HU-PAG-035','CU-35','Pagos y acceso','Cliente','acceso_cliente_modulo, planes_cliente, planes, plan_modulos'),
('RF-036','HU-COM-036','CU-36','Comunicación','Cliente Virtual / Coach','chats, chat_participantes, mensajes, planes_cliente'),
('RF-037','HU-COM-037','CU-37','Comunicación','Coach / Sistema','notificaciones, planes_cliente'),
('RF-038','HU-COM-038','CU-38','Comunicación','Sistema','notificaciones'),
('RF-039','HU-COM-039','CU-39','Comunicación','Sistema','notificaciones'),
('RF-040','HU-AGE-040','CU-40','Agenda y sesiones','Coach','sesiones, disponibilidades'),
('RF-041','HU-AGE-041','CU-41','Agenda y sesiones','Cliente','sesiones, sesion_participantes, eventos, evento_participantes, recordatorios, progreso_videos, asistencias'),
('RF-042','HU-AGE-042','CU-42','Agenda y sesiones','Sistema','recordatorios, notificaciones, sesiones, eventos, planes_cliente'),
('RF-043','HU-AGE-043','CU-43','Agenda y sesiones','Administrador / Coach','eventos, evento_participantes'),
('RF-044','HU-AGE-044','CU-44','Agenda y sesiones','Coach','sesion_participantes, evento_participantes, sesiones, eventos'),
('RF-045','HU-AGE-045','CU-45','Agenda y sesiones','Coach','disponibilidades'),
('RF-046','HU-REP-046','CU-46','Reportes y analítica','Coach','reportes_generados, registros_progreso, planes_cliente, asistencias'),
('RF-047','HU-REP-047','CU-47','Reportes y analítica','Administrador','reportes_generados, user, clientes, planes_cliente'),
('RF-048','HU-REP-048','CU-48','Reportes y analítica','Administrador','reportes_generados, pagos, transacciones_pasarela, planes_cliente'),
('RF-049','HU-REP-049','CU-49','Reportes y analítica','Administrador','reportes_generados, user, pagos, planes_cliente'),
('RF-050','HU-PAG-050','CU-50','Pagos y acceso','Sistema','transacciones_pasarela, configuracion_pasarelas, eventos_webhook, conciliaciones_pasarela, pagos, solicitudes_compra, facturas'),
('RF-051','HU-TND-051','CU-51','Tienda E-commerce','Cliente Presencial / Administrador','productos_tienda, producto_variantes, carritos, ordenes_tienda, orden_detalle, transacciones_pasarela, facturas'),
('RF-052','HU-COM-052','CU-52','Comunicación','Coach / Administrador / Cliente Presencial','configuracion_whatsapp, grupos_whatsapp, invitaciones_whatsapp, participantes_grupo_whatsapp, notificaciones, clientes'),
('RF-053','HU-PER-053','CU-53','Perfil y clientes','Cliente Presencial','medidas_antropometricas_iniciales, clientes, registros_progreso'),
('RF-054','HU-INS-054','CU-54','Instituciones','Administrador','instituciones'),
('RF-055','HU-INS-055','CU-55','Instituciones','Administrador','clientes, instituciones, enlaces_registro_institucional');
