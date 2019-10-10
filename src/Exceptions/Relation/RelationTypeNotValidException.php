<?php

namespace Click\Elements\Exceptions\Relation;

use Exception;

class RelationTypeNotValidException extends Exception
{
    public function __construct(string $type)
    {
        parent::__construct(sprintf('"%s" is not a valid relation type.', $type));
    }
}
