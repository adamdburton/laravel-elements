<?php

namespace Click\Elements\Observers;

use Click\Elements\Events\ModelSaved;
use Click\Elements\Models\Entity;

class ModelObserver
{
    public function saving(Entity $element)
    {
        event(new ModelSaved($element));
    }
}
