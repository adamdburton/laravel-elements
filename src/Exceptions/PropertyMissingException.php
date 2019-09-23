<?php

namespace Click\Elements\Exceptions;

use Exception;

class PropertyMissingException extends Exception
{
    public function __construct($property)
    {
        parent::__construct(sprintf('"%s" is not a valid property.', $property));
    }
}
