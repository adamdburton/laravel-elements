<?php

namespace Click\Elements\Exceptions;

use Exception;

class ElementsNotInstalledException extends Exception
{
    public function __construct()
    {
        parent::__construct('Please migrate your database and install Elements.');
    }
}
