<?php

namespace Click\Elements;

use Click\Elements\Concerns\HasEntityProperties;
use Click\Elements\Contracts\EntityContract;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Support\Str;

abstract class Schema implements EntityContract
{
    use HasEntityProperties;
    use HasAttributes;

    /** @return string */
    function getEntityLabel()
    {
        return $this->entityLabel ?? Str::pluralStudly($this->getEntityType());
    }

    /** @return string */
    function getEntityType()
    {
        return $this->entityType ?? Str::snake(class_basename($this));
    }
}