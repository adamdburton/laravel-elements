<?php

namespace Click\Elements\Events\Element;

use Click\Elements\Element;
use Illuminate\Queue\SerializesModels;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Triggered when an Element is saved or updated.
 */
class ElementSaved extends Event
{
    use SerializesModels;

    /**
     * @var Element
     */
    public $element;

    /**
     * @param Element $element
     */
    public function __construct(Element $element)
    {
        $this->element = $element;
    }
}
