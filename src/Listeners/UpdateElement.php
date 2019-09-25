<?php

namespace Click\Elements\Listeners;

use Click\Elements\Concerns\TwoWayBinding;
use Click\Elements\Events\ModelSaved;

class UpdateElement
{
    /**
     * @param ModelSaved $event
     * @return void
     */
    public function handle(ModelSaved $event)
    {
        $model = $event->model;

        if (in_array(TwoWayBinding::class, class_uses($model))) {
            if ($model->syncFromModel) {
                $mapping = $model->mapForElement();
            }
        }
    }
}
