<?php

namespace Click\Elements\Exceptions\Attribute;

use Click\Elements\Schemas\ElementSchema;
use Exception;

class AttributeKeyInvalidException extends Exception
{
    /**
     * @see ElementSchema::validateKey()
     */
    public function __construct(string $key)
    {
        parent::__construct(sprintf('"%s" is a not a valid format for an attribute key.', $key));
    }
}
