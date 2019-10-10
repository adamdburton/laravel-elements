<?php

namespace Click\Elements\Exceptions\Relation;

use Exception;

class ManyRelationInvalidException extends Exception
{
    public function __construct(string $key, string $elementClass, $value)
    {
        $types = is_array($value) ? array_map(function ($value) {
            return gettype($value);
        }, $value) : [$value];

        parent::__construct(
            sprintf(
                'Relation "%s" can only be set to an array of "%s" instances, "%s" given.',
                $key,
                $elementClass,
                gettype($value) . ' (' . implode(', ', $types) . ')'
            )
        );
    }
}
