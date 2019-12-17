<?php

namespace Click\Elements\Exceptions\Property;

use Exception;

class DoubleDecimalsNotValidException extends Exception
{
    public function __construct(int $decimals)
    {
        parent::__construct(sprintf('Decimals for double must be between 1 and 5, "%s" supplied.', $decimals));
    }
}
