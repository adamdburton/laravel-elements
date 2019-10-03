<?php

namespace Click\Elements\Exceptions;

use Click\Elements\Definitions\PropertyDefinition;
use Exception;

class PropertyNotInstalledException extends Exception
{
    public function __construct(string $key)
    {
        parent::__construct(sprintf('"%s" is not an installed property.', $key));
    }
}
