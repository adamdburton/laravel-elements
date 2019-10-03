<?php

namespace Click\Elements\Types;

use Click\Elements\Exceptions\RelationTypeInvalidException;
use Click\Elements\Type;

/**
 * Defines the available relation types for elements.
 */
class RelationType extends Type
{
    public const BELONGS_TO = 'belongs_to';


    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::BELONGS_TO,
        ];
    }

    /**
     * @param string $key
     * @param string $type
     * @param $value
     * @throws RelationTypeInvalidException
     */
    public static function validateValue(string $key, $type, $value)
    {
        if (!in_array($type, self::getTypes())) {
            throw new RelationTypeInvalidException($type);
        }
    }
}
