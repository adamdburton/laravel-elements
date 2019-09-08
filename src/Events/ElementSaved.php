<?php

namespace Click\Elements\Events;

use Click\Elements\Entity;
use Illuminate\Queue\SerializesModels;

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
