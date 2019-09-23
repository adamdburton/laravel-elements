<?php

namespace Click\Elements\Exceptions;

use Exception;

class InvalidPropertyValueException extends Exception
{
    public function __construct($class)
    {
        parent::__construct(sprintf('Property "%s" is not def.', $class));
    }
}
