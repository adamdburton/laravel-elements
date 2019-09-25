<?php

namespace Click\Elements\Exceptions;

use Click\Elements\Definitions\PropertyDefinition;
use Exception;

class PropertyMissingException extends Exception
{
    public function __construct(PropertyDefinition $propertyDefinition)
    {dd($propertyDefinition);
        parent::__construct(sprintf('"%s" is not a valid property.', $typedProperty->key));
    }
}
