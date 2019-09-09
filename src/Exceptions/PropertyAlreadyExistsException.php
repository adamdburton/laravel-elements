<?php

namespace Click\Elements\Exceptions;

use Exception;

class PropertyAlreadyExistsException extends Exception
{
    public function __construct($property)
    {
        parent::__construct(sprintf('"%s" is already a property.', $property));
    }
}
