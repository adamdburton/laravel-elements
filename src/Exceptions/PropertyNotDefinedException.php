<?php

namespace Click\Elements\Exceptions;

use Exception;

class PropertyNotDefinedException extends Exception
{
    public function __construct($type)
    {
        parent::__construct(sprintf('"%s" is not a defined property.', $type));
    }
}
