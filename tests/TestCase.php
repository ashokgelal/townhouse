<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
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
}
