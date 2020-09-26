<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Events\Element\ElementCreated;
use Click\Elements\Events\Element\ElementCreating;
use Click\Elements\Events\Element\ElementDeleted;
use Click\Elements\Events\Element\ElementDeleting;
use Click\Elements\Events\Element\ElementSaved;
use Click\Elements\Events\Element\ElementSaving;
use Click\Elements\Events\Element\ElementUpdated;
use Click\Elements\Events\Element\ElementUpdating;
use Click\Elements\Exceptions\Event\InvalidEventException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Trait RaisesEvents
 */
trait RaisesEvents
{
    /**
     * @param string $eventName
     * @return Event
     */
    protected function fireEvent(string $eventName)
    {
        try {
            event($event = $this->resolveEvent($eventName));
        } catch (BindingResolutionException $e) {
            // Impossibru!
        }

        return $event;
    }

    /**
     * @param string $eventName
     * @return Event
     */
    protected function resolveEvent(string $eventName)
    {
        switch ($eventName) {
            case 'saving':
                return new ElementSaving($this->element);
            case 'saved':
                return new ElementSaved($this->element);
            case 'updating':
                return new ElementUpdating($this->element);
            case 'updated':
                return new ElementUpdated($this->element);
            case 'creating':
                return new ElementCreating($this->element);
            case 'created':
                return new ElementCreated($this->element);
            case 'deleting':
                return new ElementDeleting($this->element);
            case 'deleted':
                return new ElementDeleted($this->element);
        }

//        throw new InvalidEventException($eventName);
    }
}
