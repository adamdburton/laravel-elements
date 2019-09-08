<?php

namespace Click\Elements;

use App\Listeners\UpdateElements;
use Carbon\Laravel\ServiceProvider;
use Click\Elements\Elements\Field;
use Click\Elements\Elements\FieldGroup;
use Click\Elements\Elements\FieldType;
use Click\Elements\Events\ModelSaved;
use Click\Elements\Models\Entity;
use Click\Elements\Observers\EntityObserver;
use Click\Elements\Schemas\Element;
use Illuminate\Support\Facades\Event;

class ElementsServiceProvider extends ServiceProvider
{
    protected $entities = [
        Element::class
    ];

    protected $elements = [
        Field::class,
        FieldGroup::class,
        FieldType::class
    ];

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
        $this->bootElements();
        $this->bootEntities();

        $this->bootListeners();
        $this->bootObservers();

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * @return void
     */
    protected function bootForConsole()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
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

    protected function bootEntities()
    {
        foreach ($this->entities as $entity) {
//            elements()->entities()->define($entity);
        }
    }

    protected function bootElements()
    {
        foreach ($this->elements as $element) {
//            elements()->elements()->define($element);
        }
    }

    /**
     * @return void
     */
    public function register()
    {
//        $this->registerServices();
    }

    protected function registerServices()
    {

    }
}
