<?php

namespace Click\Elements\Observers;

use Click\Elements\Models\Entity;
use Str;

class EntityObserver
{
    public function creating(Entity $entity)
    {
        if (!$entity->uuid) {
            $entity->uuid = Str::orderedUuid();
        }
    }
}
