<?php

namespace Click\Elements\Benchmarks\Assets\Elements;

use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Exceptions\AttributeSchema\AttributeSchemaClassInvalidException;
use Click\Elements\Schemas\ElementSchema;

class Author extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function buildDefinition(ElementSchema $schema)
    {
        $schema->string('name');
        $schema->unsignedInteger('born');
    }
}
