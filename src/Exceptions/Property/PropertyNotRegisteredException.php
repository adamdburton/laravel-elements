<?php

namespace Click\Elements\Exceptions\Property;

use Exception;

class PropertyNotRegisteredException extends Exception
{
    public function __construct(string $key)
    {
        parent::__construct(sprintf('"%s" is not a valid property.', $key));
    }
}
