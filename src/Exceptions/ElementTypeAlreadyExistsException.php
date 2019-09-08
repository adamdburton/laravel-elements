<?php

namespace Click\Elements\Exceptions;

use Exception;

class ElementTypeAlreadyExistsException extends Exception
{
    public function __construct($type)
    {
        parent::__construct(sprintf('"%s" is already a defined element type.', $type));
    }
}
