<?php

namespace Click\Elements\Exceptions;

use Exception;

class TablesMissingException extends Exception
{
    public function __construct($class)
    {
        parent::__construct('Please migrate your database before installing elements.');
    }
}
