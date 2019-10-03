<?php

namespace Click\Elements\Exceptions\Element;

use Exception;

class ElementNotRegisteredException extends Exception
{
    public function __construct($type)
    {
        parent::__construct(sprintf('"%s" is not a registered element type.', $type));
    }
}
