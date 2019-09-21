<?php

namespace Click\Elements\Listeners;

use Click\Elements\Events\ModelSaved;
use Click\Elements\Services\ElementService;

class UpdateElements
{
    /**
     * @var ElementService
     */
    protected $elementService;

    /**
     * @param ElementService $elementService
     */
    public function __construct(ElementService $elementService)
    {
        $this->elementService = $elementService;
    }

    /**
     * @param ModelSaved $event
     * @return void
     */
    public function handle(ModelSaved $event)
    {
        // Access the order using $event->order...
    }
}
