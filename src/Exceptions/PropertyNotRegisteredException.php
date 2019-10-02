<?php

namespace Click\Elements\Exceptions;

use Click\Elements\Definitions\PropertyDefinition;
use Exception;

class PropertyNotRegisteredException extends Exception
{
    public function __construct(string $key)
    {
        parent::__construct(sprintf('"%s" is not a valid property.', $key));
    }
}
