<?php

namespace Click\Elements;

use Click\Elements\Commands\InstallElements;
use Click\Elements\Commands\MakeElement;
use Click\Elements\Elements\ElementType;
use Click\Elements\Elements\Module;
use Click\Elements\Elements\TypedProperty;
use Click\Elements\Events\ModelSaved;
use Click\Elements\Listeners\UpdateElements;
use Click\Elements\Models\Entity;
use Click\Elements\Observers\EntityObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel Service Provider
 */
class ElementsServiceProvider extends ServiceProvider
{
    protected $listeners = [
        ModelSaved::class => [
            UpdateElements::class
        ]
    ];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootListeners();
        $this->bootObservers();

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * @return void
     */
    protected function bootListeners()
    {
        foreach ($this->listeners as $event => $listeners) {
            foreach (array_unique($listeners) as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * @return void
     */
    protected function bootObservers()
    {
        Entity::observe(EntityObserver::class);
    }

    /**
     * @return void
     */
    protected function bootForConsole()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->commands([
            InstallElements::class,
            MakeElement::class
        ]);
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->registerServices();
    }

    protected function registerServices()
    {
        $this->app->singleton(Elements::class, function ($app) {
            return new Elements;
        });
    }
}
