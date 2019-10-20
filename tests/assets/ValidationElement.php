<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Exceptions\Property\PropertyAlreadyDefinedException;
use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Schemas\ElementSchema;

class ValidationElement extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws PropertyAlreadyDefinedException
     * @throws PropertyKeyInvalidException
     */
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('string')->validation('required');
        $schema->string('email')->validation('sometimes|email');
        $schema->json('json');
    }
}