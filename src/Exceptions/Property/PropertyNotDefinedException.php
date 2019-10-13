<?php

namespace Click\Elements\Exceptions\Property;

use Click\Elements\Definitions\ElementDefinition;
use Exception;

class PropertyNotDefinedException extends Exception
{
    public function __construct($key, ElementDefinition $definition)
    {
        parent::__construct(sprintf(
            'The key "%s" is not defined for %s.',
            $key,
            $definition->getAlias()
        ));
    }
}
