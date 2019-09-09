<?php

namespace Click\Elements\Tests;

use Click\Elements\ElementsServiceProvider;
use Click\Elements\Facades\Elements;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../database/factories');

        $this->artisan('migrate:fresh', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__ . '/../database/migrations'),
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [ElementsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => realpath(__DIR__ . '/testing.sqlite'),
            'prefix' => '',
        ]);
    }

    protected function assertRouteExists($route)
    {
        $this->assertTrue(\Route::has($route), 'Route \'' . $route . '\' does not exist');
    }
}
