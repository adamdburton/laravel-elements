<?php

namespace Click\Elements\Exceptions;

use Click\Elements\Definitions\ElementDefinition;
use Exception;

class SchemaPropertyAlreadyDefined extends Exception
{
    public function __construct($key, ElementDefinition $elementType)
    {
        $type = $elementType->getType();

        parent::__construct(sprintf('The key "%s" has already been defined for element type "%s".', $key, $type));
    }
}
