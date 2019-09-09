<?php

namespace Click\Elements\Exceptions;

use Exception;

class PropertyMissingException extends Exception
{
    public function __construct($property)
    {
        parent::__construct(sprintf('"%s" property is missing.', $property));
    }
}
