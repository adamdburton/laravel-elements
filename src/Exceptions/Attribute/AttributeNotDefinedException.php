<?php

namespace Click\Elements\Exceptions\Attribute;

use Click\Elements\Definitions\ElementDefinition;
use Exception;

class AttributeNotDefinedException extends Exception
{
    public function __construct($key, ElementDefinition $definition)
    {
        parent::__construct(sprintf(
            'Attribute "%s" is not defined for element "%s".',
            $key,
            $definition->getAlias()
        ));
    }
}
