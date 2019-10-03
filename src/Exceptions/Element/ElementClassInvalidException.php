<?php

namespace Click\Elements\Exceptions\Element;

use Exception;

class ElementClassInvalidException extends Exception
{
    public function __construct($class)
    {
        parent::__construct(sprintf('"%s" must derive from Click\\Elements\\Element.', $class));
    }
}
