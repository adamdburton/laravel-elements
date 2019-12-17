<?php

namespace Click\Elements\Exceptions\AttributeSchema;

use Exception;

class AttributeSchemaClassInvalidException extends Exception
{
    public function __construct($class)
    {
        parent::__construct(sprintf('"%s" must derive from Click\\Elements\\Schemas\\AttributeSchema.', $class));
    }
}
