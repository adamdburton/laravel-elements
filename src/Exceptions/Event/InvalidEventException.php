<?php

namespace Click\Elements\Exceptions\Event;

use Exception;

class InvalidEventException extends Exception
{
    public function __construct($event)
    {
        parent::__construct(sprintf('"%s" is not a valid Element event.', $event));
    }
}