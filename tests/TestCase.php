<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = (string) config('database.connections.mysql.database');
        if ($db === 'stayfit') {
            $this->fail(
                'BLOQUEADO: los tests no pueden ejecutarse contra la BD stayfit (datos reales). '
                .'Usa stayfit_test (phpunit.xml).'
            );
        }
    }
}
