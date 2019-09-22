<?php

namespace Click\Elements\Tests;

use Click\Elements\Elements;
use Click\Elements\ElementsServiceProvider;
use Click\Elements\Tests\Elements\TestElement;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var Elements */
    protected $elements;

    /** @var bool */
    protected $elementsInstalled = true;

    /** @var bool */
    protected $testElementInstalled = true;

    public function setUp(): void
    {
        parent::setUp();

        $this->elements = app(Elements::class);

        $this->withFactories(__DIR__ . '/../database/factories');

        $this->artisan('migrate:fresh', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__ . '/../database/migrations'),
        ]);

        if ($this->elementsInstalled) {
            $this->artisan('elements:install');
        }

        if ($this->testElementInstalled) {
            $this->elements->register(TestElement::class)->install();
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
            'driver' => 'sqlite',
            'database' => realpath(__DIR__ . '/../database/testing.sqlite'),
            'prefix' => '',
        ]);
    }
}
