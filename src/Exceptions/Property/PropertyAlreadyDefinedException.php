<?php

namespace Click\Elements\Exceptions\Property;

use Click\Elements\Definitions\ElementDefinition;
use Exception;

class PropertyAlreadyDefinedException extends Exception
{
    public function __construct($key, ElementDefinition $elementType)
    {
        $type = $elementType->getAlias();

        parent::__construct(sprintf('The key "%s" has already been defined for element "%s".', $key, $type));
    }
}
