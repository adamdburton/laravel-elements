<?php

namespace Click\Elements;

use Click\Elements\Commands\InstallElements;
use Click\Elements\Commands\MakeElement;
use Click\Elements\Events\ModelSaved;
use Click\Elements\Listeners\UpdateElement;
use Click\Elements\Models\Entity;
use Click\Elements\Observers\EntityObserver;
use Click\Elements\Observers\ModelObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel Service Provider
 */
class ElementsServiceProvider extends ServiceProvider
{
    protected $listeners = [
        ModelSaved::class => [
            UpdateElement::class
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

        if (config('elements.auto_install')) {
            app(Elements::class)->install();
        }

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
//        Model::observe(ModelObserver::class);
        Entity::observe(EntityObserver::class);
    }

    /**
     * @return void
     */
    protected function bootForConsole()
    {
        $this->publishes([
            __DIR__ . '/../config/elements.php' => config_path('elements.php'),
        ], 'elements.config');

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
        $this->mergeConfigFrom(__DIR__ . '/../config/elements.php', 'elements');

        $this->app->singleton(Elements::class, function ($app) {
            return new Elements();
        });
    }
}
