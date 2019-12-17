<?php

namespace Click\Elements\Exceptions\Attribute;

use Exception;

class AttributeValueTypeInvalidException extends Exception
{
    public function __construct(string $element, string $key, string $type, $value)
    {
        parent::__construct(sprintf(
            'Attribute "%s" for element "%s" must be of type "%s", type "%s" given.',
            $key,
            $element,
            $type,
            gettype($value) === 'object' && gettype($type) === 'string' ? $type : gettype($value)
        ));
    }
}
