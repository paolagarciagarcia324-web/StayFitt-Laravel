<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL 8.0 error 3823: a column used in a FK with referential actions
        // (ON DELETE SET NULL / ON UPDATE CASCADE) cannot also appear in a CHECK.
        // Keep the same columns, types and FK actions as stayfit_v3.sql; enforce the
        // two domain rules that reference id_institucion / id_horario via triggers.
        DB::unprepared(<<<'SQL'
CREATE TABLE clientes (
  id_cliente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL,
  id_coach INT UNSIGNED NULL,
  id_institucion INT UNSIGNED NULL,
  id_horario INT UNSIGNED NULL,
  modalidad ENUM('VIRTUAL','PRESENCIAL') NOT NULL,
  tipo_cliente ENUM('INDIVIDUAL','INSTITUCIONAL') NOT NULL DEFAULT 'INDIVIDUAL',
  cargo_institucional VARCHAR(100) NULL,
  fecha_nacimiento DATE NULL,
  genero ENUM('FEMENINO','MASCULINO','OTRO','NO_ESPECIFICA') NOT NULL DEFAULT 'NO_ESPECIFICA',
  tipo_sangre ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NULL,
  direccion VARCHAR(180) NULL,
  ciudad VARCHAR(100) NULL,
  ocupacion VARCHAR(100) NULL,
  como_conocio VARCHAR(100) NULL,
  objetivo_principal VARCHAR(180) NULL,
  nivel_actividad ENUM('SEDENTARIO','LIGERAMENTE_ACTIVO','MODERADAMENTE_ACTIVO','MUY_ACTIVO','EXTREMADAMENTE_ACTIVO') NULL DEFAULT 'SEDENTARIO',
  experiencia_previa ENUM('NINGUNA','PRINCIPIANTE','INTERMEDIO','AVANZADO') NULL DEFAULT 'NINGUNA',
  frecuencia_ejercicio_semanal TINYINT UNSIGNED NULL,
  peso_inicial_kg DECIMAL(5,2) NULL,
  estatura_cm DECIMAL(5,2) NULL,
  alergias TEXT NULL,
  condiciones_medicas TEXT NULL,
  lesiones TEXT NULL,
  medicamentos TEXT NULL,
  contacto_emergencia_nombre VARCHAR(120) NULL,
  contacto_emergencia_telefono VARCHAR(30) NULL,
  contacto_emergencia_parentesco VARCHAR(50) NULL,
  racha_actual INT UNSIGNED NOT NULL DEFAULT 0,
  racha_maxima INT UNSIGNED NOT NULL DEFAULT 0,
  ultima_racha_fecha DATE NULL,
  estado_cliente ENUM('ACTIVA','INACTIVA','SUSPENDIDA','PENDIENTE') NOT NULL DEFAULT 'PENDIENTE',
  fecha_alta DATE NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_clientes_user (id_user),
  KEY idx_clientes_coach (id_coach),
  KEY idx_clientes_institucion (id_institucion),
  KEY idx_clientes_modalidad (modalidad, tipo_cliente, estado_cliente),
  CONSTRAINT chk_clientes_peso CHECK (peso_inicial_kg IS NULL OR (peso_inicial_kg >= 20 AND peso_inicial_kg <= 400)),
  CONSTRAINT chk_clientes_estatura CHECK (estatura_cm IS NULL OR (estatura_cm >= 50 AND estatura_cm <= 280)),
  CONSTRAINT chk_clientes_frecuencia CHECK (frecuencia_ejercicio_semanal IS NULL OR frecuencia_ejercicio_semanal <= 14),
  CONSTRAINT fk_clientes_usuario
    FOREIGN KEY (id_user) REFERENCES usuarios(id_user)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_clientes_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_clientes_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_clientes_horario
    FOREIGN KEY (id_horario) REFERENCES horarios(id_horario)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_clientes_chk_domain_bi
BEFORE INSERT ON clientes
FOR EACH ROW
BEGIN
  IF NOT (
    (NEW.tipo_cliente = 'INDIVIDUAL' AND NEW.id_institucion IS NULL)
    OR (NEW.tipo_cliente = 'INSTITUCIONAL')
  ) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'chk_clientes_institucional: INDIVIDUAL requiere id_institucion NULL';
  END IF;

  IF NOT (
    (NEW.modalidad = 'VIRTUAL' AND NEW.id_horario IS NULL)
    OR (NEW.modalidad = 'PRESENCIAL')
  ) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'chk_clientes_horario_presencial: VIRTUAL requiere id_horario NULL';
  END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_clientes_chk_domain_bu
BEFORE UPDATE ON clientes
FOR EACH ROW
BEGIN
  IF NOT (
    (NEW.tipo_cliente = 'INDIVIDUAL' AND NEW.id_institucion IS NULL)
    OR (NEW.tipo_cliente = 'INSTITUCIONAL')
  ) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'chk_clientes_institucional: INDIVIDUAL requiere id_institucion NULL';
  END IF;

  IF NOT (
    (NEW.modalidad = 'VIRTUAL' AND NEW.id_horario IS NULL)
    OR (NEW.modalidad = 'PRESENCIAL')
  ) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'chk_clientes_horario_presencial: VIRTUAL requiere id_horario NULL';
  END IF;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_clientes_chk_domain_bu');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_clientes_chk_domain_bi');
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `clientes`;
SQL);
    }
};
