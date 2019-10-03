<?php

namespace Click\Elements\Exceptions;

use Click\Elements\Definitions\PropertyDefinition;
use Exception;

class InvalidPropertyValueException extends Exception
{
    public function __construct(PropertyDefinition $definition, $value)
    {
        parent::__construct(sprintf(
            'Property "%s" type for element "%s" must be "%s", "%s" given.',
            $definition->getKey(),
            $definition->getElementDefinition()->getAlias(),
            $definition->getType(),
            gettype($value)
        ));
    }
}
