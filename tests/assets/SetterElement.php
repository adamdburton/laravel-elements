<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Builder;
use Click\Elements\Element;
use Click\Elements\Exceptions\Property\PropertyAlreadyDefinedException;
use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Schemas\ElementSchema;

class SetterElement extends Element
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
     * @param string $status
     */
    public function setStatusAttribute(string $status)
    {
        $this->attributes['status'] = strtoupper($status);
    }
}
