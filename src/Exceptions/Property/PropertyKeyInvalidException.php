<?php

namespace Click\Elements\Exceptions\Property;

use Exception;

class PropertyKeyInvalidException extends Exception
{
    public function __construct(string $key)
    {
        parent::__construct(sprintf('"%s" is a not a valid format for a key name.', $key));
    }
}
