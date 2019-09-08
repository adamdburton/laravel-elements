<?php

namespace Click\Elements\Exceptions;

use Exception;

class ElementNotDefinedException extends Exception
{
    public function __construct($type)
    {
        parent::__construct(sprintf('"%s" is not a defined element type.', $type));
    }

}
