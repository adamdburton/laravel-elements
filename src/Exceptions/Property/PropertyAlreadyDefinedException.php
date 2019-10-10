<?php

namespace Click\Elements\Exceptions\Property;

use Exception;

class PropertyAlreadyDefinedException extends Exception
{
    public function __construct($key)
    {
        parent::__construct(sprintf('The key "%s" has already been defined in the schema.', $key));
    }
}
