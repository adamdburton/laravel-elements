<?php

namespace Click\Elements\Exceptions;

use Exception;

class PropertyValueInvalidException extends Exception
{
    public function __construct($key, $type, $value)
    {
        parent::__construct(sprintf(
            'Property "%s" must be "%s", "%s" given.',
            $key,
            $type,
            gettype($value)
        ));
    }
}
