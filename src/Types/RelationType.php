<?php

namespace Click\Elements\Types;

use Click\Elements\Exceptions\Property\RelationTypeInvalidException;
use Click\Elements\Type;

/**
 * Defines the available relation types for elements.
 */
class RelationType extends Type
{
    public const BELONGS_TO = 'belongs_to';

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

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::BELONGS_TO,
        ];
    }
}
