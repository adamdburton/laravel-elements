<?php

namespace Click\Elements\Observers;

use Click\Elements\Events\ModelSaved;
use Click\Elements\Models\Entity;

/**
 * Watches for entity saving and updating to replicate to elements
 */
class ModelObserver
{
    public function saving(Entity $element)
    {
        event(new ModelSaved($element));
    }
}
