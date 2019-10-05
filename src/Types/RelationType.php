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
    public const BELONGS_TO_MANY = 'belongs_to_many';

    /**
     * @param string $key
     * @param string $type
     * @param $value
     * @throws RelationTypeInvalidException
     */
    public static function validateValue($type)
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
            self::BELONGS_TO_MANY
        ];
    }
}
