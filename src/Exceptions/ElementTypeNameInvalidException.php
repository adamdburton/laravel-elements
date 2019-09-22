<?php

namespace Click\Elements\Exceptions;

use Exception;

class ElementTypeNameInvalidException extends Exception
{
    public function __construct($type)
    {
        parent::__construct(sprintf('"%s" is not a valid element type name.', $type));
    }
}
