<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Builder;
use Click\Elements\Element;
use Click\Elements\Exceptions\Property\PropertyAlreadyDefinedException;
use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Schemas\ElementSchema;

class GetterElement extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws PropertyAlreadyDefinedException
     * @throws PropertyKeyInvalidException
     */
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('status');
    }

    /**
     * @return string
     */
    public function getStatusAttribute()
    {
        return strtoupper($this->attributes['status']);
    }
}
