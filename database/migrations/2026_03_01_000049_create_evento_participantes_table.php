<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE evento_participantes (
  id_evento_participante INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_evento INT UNSIGNED NOT NULL,
  id_cliente INT UNSIGNED NULL,
  id_pago INT UNSIGNED NULL,
  nombre_invitado VARCHAR(150) NULL,
  correo_invitado VARCHAR(150) NULL,
  telefono_invitado VARCHAR(30) NULL,
  confirmado TINYINT(1) NOT NULL DEFAULT 0,
  codigo_entrada VARCHAR(20) NULL,
  estado_participacion ENUM('INSCRITA','ASISTIO','NO_ASISTIO','CANCELADA','LISTA_ESPERA') NOT NULL DEFAULT 'INSCRITA',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_evento_cliente (id_evento, id_cliente),
  UNIQUE KEY uq_evento_codigo (codigo_entrada),
  CONSTRAINT fk_ep_evento FOREIGN KEY (id_evento) REFERENCES eventos(id_evento) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ep_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ep_pago FOREIGN KEY (id_pago) REFERENCES pagos(id_pago) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `evento_participantes`;
SQL);
    }
};
