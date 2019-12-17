<?php

namespace Click\Elements\Exceptions\Attribute;

use Click\Elements\Schemas\ElementSchema;
use Exception;

class AttributeAlreadyDefinedException extends Exception
{
    public function __construct($key, ElementSchema $elementSchema)
    {
        parent::__construct(sprintf(
            'Attribute "%s" has already been defined in the schema for "%s".',
            $key,
            $elementSchema->getElement()->getAlias()
        ));
    }
}
