<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Schemas\ElementSchema;

class ValidationElement extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     */
    public function buildDefinition(ElementSchema $schema)
    {
        $schema->string('string')->validation('required');
        $schema->string('email')->validation('sometimes|email');
        $schema->json('json');
    }
}