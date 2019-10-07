<?php

namespace Click\Elements\Exceptions\Relation;

use Exception;

class SingleRelationInvalidException extends Exception
{
    public function __construct(string $key, string $elementClass, $value)
    {
        parent::__construct(
            sprintf(
                'Relation "%s" can only be set to an instance of "%s", "%s" given.',
                $key,
                $elementClass,
                gettype($value)
            )
        );
    }
}
