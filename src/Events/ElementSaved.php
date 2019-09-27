<?php

namespace Click\Elements\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Triggered when an Element is saved or updated.
 */
class ElementSaved
{
    use SerializesModels;

    /**
     * @var Entity
     */
    public $element;

    /**
     * Create a new event instance.
     *
     * @param Entity $element
     */
    public function __construct(Entity $element)
    {
        $this->element = $element;
    }
}
