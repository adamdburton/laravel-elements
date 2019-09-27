<?php

namespace Click\Elements\Observers;

use Click\Elements\Models\Entity;

/**
 * Watches for entity saving and updating to replicate to models
 */
class EntityObserver
{
    public function creating(Entity $entity)
    {
    }
}
