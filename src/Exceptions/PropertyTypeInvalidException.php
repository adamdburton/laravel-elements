<?php

namespace Click\Elements\Exceptions;

use Exception;

class PropertyTypeInvalidException extends Exception
{
    public function __construct($type)
    {
        parent::__construct(sprintf('"%s" is not a valid property type.', $type));
    }

}
