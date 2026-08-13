<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
ALTER TABLE pagos
  ADD CONSTRAINT fk_pagos_plan_cliente
    FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente)
    ON DELETE SET NULL ON UPDATE CASCADE;
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE suscripciones_recurrentes
  ADD CONSTRAINT fk_susc_plan_cliente
    FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente)
    ON DELETE RESTRICT ON UPDATE CASCADE;
SQL);

        DB::unprepared(<<<'SQL'
ALTER TABLE planes_cliente
  ADD CONSTRAINT fk_pc_pago
    FOREIGN KEY (id_pago) REFERENCES pagos(id_pago)
    ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_pc_suscripcion
    FOREIGN KEY (id_suscripcion) REFERENCES suscripciones_recurrentes(id_suscripcion)
    ON DELETE SET NULL ON UPDATE CASCADE;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
ALTER TABLE `planes_cliente` DROP FOREIGN KEY `fk_pc_pago`;
SQL);
        DB::unprepared(<<<'SQL'
ALTER TABLE `planes_cliente` DROP FOREIGN KEY `fk_pc_suscripcion`;
SQL);
        DB::unprepared(<<<'SQL'
ALTER TABLE `suscripciones_recurrentes` DROP FOREIGN KEY `fk_susc_plan_cliente`;
SQL);
        DB::unprepared(<<<'SQL'
ALTER TABLE `pagos` DROP FOREIGN KEY `fk_pagos_plan_cliente`;
SQL);
    }
};
