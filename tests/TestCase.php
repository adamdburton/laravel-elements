<?php

namespace Click\Elements\Tests;

use Click\Elements\Elements;
use Click\Elements\ElementsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Class TestCase
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var Elements
     */
    protected $elements;

    /**
     * @var bool
     */
    protected $elementsInstalled = true;

    public function setUp(): void
    {
        parent::setUp();

        $this->elements = app(Elements::class);
        
        $this->artisan('migrate:fresh', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__ . '/../database/migrations'),
        ]);

        if ($this->elementsInstalled) {
            $this->artisan('elements:install');
        }
    }

    protected function getPackageProviders($app)
    {
        return [ElementsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'testing',
            'username' => 'root',
            'password' => '',
            'prefix' => '',
        ]);
    }
}
