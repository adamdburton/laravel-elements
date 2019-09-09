<?php

namespace Click\Elements;

use Click\Elements\Concerns\HasAttributes;
use Click\Elements\Contracts\EntityContract;
use Illuminate\Support\Str;

abstract class Schema implements EntityContract
{
    use HasAttributes;

    protected $entityLabel;

    protected $entityType;

    /** @return string */
    public function getEntityLabel()
    {
        return $this->entityLabel ?: ucwords(Str::pluralStudly($this->getEntityType()));
    }

    /** @return string */
    public function getEntityType()
    {
        return $this->entityType ?: Str::lower(Str::snake(class_basename($this)));
    }

    /**
     * @return array
     */
    public function getEntityProperties()
    {
        return collect($this->getProperties())->map(function ($config, $key) {
            return elements()->properties()->getPropertyForEntity($this, $key);
        })->all();
    }
}
