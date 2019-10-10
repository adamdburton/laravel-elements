<?php

namespace Click\Elements\Types;

use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Type;

/**
 * Defines the available relation types for elements.
 */
class RelationType extends Type
{
    public const SINGLE = 'single';
    public const MANY = 'many';

    /**
     * @param string $type
     * @throws ManyRelationInvalidException
     */
    public static function validateValue($type)
    {
        if (!in_array($type, static::getTypes())) {
            throw new ManyRelationInvalidException($type);
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
