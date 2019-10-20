<?php

namespace Click\Elements\Benchmarks\Assets\Elements;

use Click\Elements\Element;
use Click\Elements\Exceptions\Property\PropertyAlreadyDefinedException;
use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Schemas\ElementSchema;

class Author extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws PropertyKeyInvalidException
     * @throws PropertyAlreadyDefinedException
     */
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('name');
        $schema->unsignedInteger('born');
    }
}
