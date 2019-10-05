<?php

namespace Click\Elements\Types;

use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Click\Elements\Type;

/**
 * Defines the available property types for elements.
 */
class PropertyType extends Type
{
    public const BOOLEAN = 'boolean';
    public const INTEGER = 'integer';
    public const UNSIGNED_INTEGER = '-integer';
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
            self::BOOLEAN,
            self::INTEGER,
            self::UNSIGNED_INTEGER,
            self::DOUBLE,
            self::STRING,
            self::TEXT,
            self::JSON,
            self::ARRAY,
            self::RELATION,
            self::TIMESTAMP
        ];
    }
}
