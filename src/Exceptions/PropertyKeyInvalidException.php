<?php

namespace Click\Elements\Exceptions;

use Exception;

class PropertyKeyInvalidException extends Exception
{
    public function __construct($key)
    {
        parent::__construct(sprintf('"%s" is a reserved key name.', $key));
    }
}
