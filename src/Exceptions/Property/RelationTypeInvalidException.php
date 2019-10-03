<?php

namespace Click\Elements\Exceptions\Property;

use Exception;

class RelationTypeInvalidException extends Exception
{
    public function __construct($type)
    {
        parent::__construct(sprintf('"%s" is not a valid relation type.', $type));
    }
}