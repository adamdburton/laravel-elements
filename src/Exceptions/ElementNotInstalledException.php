<?php

namespace Click\Elements\Exceptions;

use Exception;

class ElementNotInstalledException extends Exception
{
    public function __construct($type)
    {
        parent::__construct(sprintf('"%s" is not an installed element type.', $type));
    }
}
