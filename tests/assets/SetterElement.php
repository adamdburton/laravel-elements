<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Builder;
use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Schemas\ElementSchema;

class SetterElement extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     */
    public function buildDefinition(ElementSchema $schema)
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
