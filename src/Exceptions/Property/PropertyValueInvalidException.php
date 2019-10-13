<?php

namespace Click\Elements\Exceptions\Property;

use Exception;

class PropertyValueInvalidException extends Exception
{
    public function __construct($key, $type, $value)
    {
        parent::__construct(sprintf(
            'Property "%s" must be type "%s", type "%s" given.',
            $key,
            $type,
            gettype($value)
        ));
    }
}
