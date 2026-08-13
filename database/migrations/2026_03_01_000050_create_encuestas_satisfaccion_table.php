<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE encuestas_satisfaccion (
  id_encuesta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_plan_cliente INT UNSIGNED NULL,
  id_sesion INT UNSIGNED NULL,
  id_evento INT UNSIGNED NULL,
  id_coach INT UNSIGNED NULL,
  puntuacion_general TINYINT UNSIGNED NOT NULL,
  puntuacion_coach TINYINT UNSIGNED NULL,
  puntuacion_contenido TINYINT UNSIGNED NULL,
  puntuacion_instalaciones TINYINT UNSIGNED NULL,
  comentario TEXT NULL,
  recomendaria TINYINT(1) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_enc_cliente_sesion (id_cliente, id_sesion),
  UNIQUE KEY uq_enc_cliente_evento (id_cliente, id_evento),
  UNIQUE KEY uq_enc_cliente_plan (id_cliente, id_plan_cliente),
  CONSTRAINT chk_enc_gen CHECK (puntuacion_general BETWEEN 1 AND 5),
  CONSTRAINT chk_enc_coach CHECK (puntuacion_coach IS NULL OR puntuacion_coach BETWEEN 1 AND 5),
  CONSTRAINT fk_enc_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_enc_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_enc_sesion FOREIGN KEY (id_sesion) REFERENCES sesiones(id_sesion) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_enc_evento FOREIGN KEY (id_evento) REFERENCES eventos(id_evento) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_enc_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `encuestas_satisfaccion`;
SQL);
    }
};
