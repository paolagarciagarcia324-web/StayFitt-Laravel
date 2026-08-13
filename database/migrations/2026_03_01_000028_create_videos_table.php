<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE videos (
  id_video INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_programa INT UNSIGNED NOT NULL,
  id_categoria INT UNSIGNED NULL,
  id_coach INT UNSIGNED NULL,
  dia_orden INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Dia 1 desbloqueado; N requiere completar N-1',
  titulo VARCHAR(160) NOT NULL,
  descripcion TEXT NULL,
  tipo_video ENUM('EJERCICIO','NUTRICIONAL','INFORMATIVO','MOTIVACIONAL','RECETA','TUTORIAL') NOT NULL DEFAULT 'EJERCICIO',
  visibilidad ENUM('PUBLICO','PRIVADO','POR_PLAN') NOT NULL DEFAULT 'POR_PLAN',
  tags VARCHAR(255) NULL,
  url_video VARCHAR(500) NOT NULL,
  imagen_portada_url VARCHAR(500) NULL,
  archivo_subtitulos VARCHAR(500) NULL,
  duracion_segundos INT UNSIGNED NULL,
  segundos_explicacion INT UNSIGNED NULL COMMENT 'Hasta cuando dura la explicacion antes de modo IA',
  series_objetivo TINYINT UNSIGNED NULL,
  repeticiones_objetivo INT UNSIGNED NULL,
  requiere_validacion_ia TINYINT(1) NOT NULL DEFAULT 1,
  orden INT NOT NULL DEFAULT 0,
  estado_video ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_video_programa_dia (id_programa, dia_orden),
  KEY idx_videos_programa (id_programa, estado_video),
  CONSTRAINT chk_videos_dia CHECK (dia_orden >= 1),
  CONSTRAINT fk_videos_programa
    FOREIGN KEY (id_programa) REFERENCES programas_virtuales(id_programa)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_videos_categoria
    FOREIGN KEY (id_categoria) REFERENCES categorias_video(id_categoria)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_videos_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `videos`;
SQL);
    }
};
