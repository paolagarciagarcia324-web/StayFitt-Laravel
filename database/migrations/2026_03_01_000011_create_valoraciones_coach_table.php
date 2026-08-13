<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE valoraciones_coach (
  id_valoracion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_coach INT UNSIGNED NOT NULL,
  puntuacion TINYINT UNSIGNED NOT NULL,
  comentario TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_valoracion_cliente_coach (id_cliente, id_coach),
  CONSTRAINT chk_valoracion_puntuacion CHECK (puntuacion BETWEEN 1 AND 5),
  CONSTRAINT fk_valoraciones_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_valoraciones_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `valoraciones_coach`;
SQL);
    }
};
