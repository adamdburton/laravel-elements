<?php

namespace Click\Elements\Exceptions;

use Click\Elements\ElementDefinition;
use Exception;

class SchemaPropertyAlreadyDefined extends Exception
{
    public function __construct($key, ElementDefinition $elementType)
    {
        parent::__construct(sprintf('The key "%s" has already been defined for element type "%s".', $key, $elementType->getType()));
    }
}
