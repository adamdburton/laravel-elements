<?php

namespace Click\Elements\Tests;

use Click\Elements\Commands\InstallElements;
use Click\Elements\Elements;
use Click\Elements\ElementsServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Class TestCase
 * @\PhpBench\Benchmark\Metadata\Annotations\BeforeMethods({"setUp"})
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

        if ($this->elementsInstalled) {
            $this->runElementsMigration();

            $this->artisan(InstallElements::class);
        }
    }

    /**
     * Get package providers.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ElementsServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => 'mysql',
            'database' => 'testing',
            'username' => 'testing',
            'password' => 'testing',
            'prefix' => '',
        ]);
    }

    protected function runElementsMigration()
    {
        $this->artisan('migrate:fresh', [
            '--database' => 'mysql',
            '--realpath' => realpath(__DIR__ . '/../database/migrations'),
        ]);
    }
}
