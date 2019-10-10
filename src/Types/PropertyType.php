<?php

namespace Click\Elements\Types;

use Click\Elements\Type;

/**
 * Defines the available property types for elements.
 */
class PropertyType extends Type
{
    public const BOOLEAN = 'boolean';
    public const INTEGER = 'integer';
    public const UNSIGNED_INTEGER = 'unsigned_integer';
    public const DOUBLE = 'double';
    public const STRING = 'string';
    public const TEXT = 'text';
    public const ARRAY = 'array';
    public const JSON = 'json';
    public const RELATION = 'relation';
    public const TIMESTAMP = 'timestamp';

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            static::BOOLEAN,
            static::INTEGER,
            static::UNSIGNED_INTEGER,
            static::DOUBLE,
            static::STRING,
            static::TEXT,
            static::JSON,
            static::ARRAY,
            static::RELATION,
            static::TIMESTAMP
        ];
    }
}
