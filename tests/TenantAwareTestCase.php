<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TenantAwareTestCase extends TestCase
{
    use RefreshDatabase;

    protected function refreshApplication()
    {
        parent::refreshApplication();
        $this->artisan('tenancy:install');
    }

    protected function assertSystemDatabaseHas($table, array $data)
    {
        $this->assertDatabaseHas($table, $data, env('DB_CONNECTION'));
    }

    protected function assertSystemDatabaseMissing($table, array $data)
    {
        $this->assertDatabaseMissing($table, $data, env('DB_CONNECTION'));
    }
}
