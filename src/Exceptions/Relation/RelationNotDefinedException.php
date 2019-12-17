<?php

namespace Click\Elements\Exceptions\Relation;

use Click\Elements\Definitions\ElementDefinition;
use Exception;

class RelationNotDefinedException extends Exception
{
    public function __construct($key, ElementDefinition $elementDefinition)
    {
        parent::__construct(sprintf(
            '"%s" is not a valid relation for element %s.',
            $key,
            $elementDefinition->getAlias()
        ));
    }
}
