<?php

namespace Click\Elements\Types;

use Click\Elements\Exceptions\Relation\RelationTypeNotValidException;
use Click\Elements\Type;

/**
 * Defines the available relation types for elements.
 */
class RelationType extends Type
{
    public const SINGLE = 'single';
    public const MANY = 'many';
    public const BELONGS_TO = 'belongs_to';

    /**
     * @param string $type
     * @throws RelationTypeNotValidException
     */
    public static function validateValue($type)
    {
        if (!in_array($type, static::getTypes())) {
            throw new RelationTypeNotValidException($type);
        }
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            static::SINGLE,
            static::MANY
        ];
    }
}
