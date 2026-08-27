<?php

namespace App\Console\Commands;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Console\Command;

class SeedDemoDataCommand extends Command
{
    protected $signature = 'demo:data {--force : Regenera/actualiza aunque demodata01 ya exista}';

    protected $description = 'Carga el bundle demo profesional StayFit (demodata01)';

    public function handle(): int
    {
        $this->info('Cargando bundle demodata01...');

        $seeder = new DemoDataSeeder;
        $seeder->setCommand($this);
        $seeder->run((bool) $this->option('force'));

        return self::SUCCESS;
    }
}
