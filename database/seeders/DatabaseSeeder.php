<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Bundle único profesional (incluye usuarios, comercial, comunidad, análisis)
        $this->call([
            DemoDataSeeder::class,
        ]);
    }
}
