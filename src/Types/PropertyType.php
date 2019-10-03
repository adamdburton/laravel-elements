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
            self::DOUBLE,
            self::STRING,
            self::TEXT,
            self::JSON,
            self::ARRAY,
            self::RELATION,
            self::TIMESTAMP
        ];
    }

    /**
     * @param string $key
     * @param string $type
     * @param $value
     * @return mixed
     * @throws PropertyValueInvalidException
     */
    public static function validateValue(string $key, string $type, $value)
    {
        switch ($type) {
            case PropertyType::JSON:
                $type = PropertyType::ARRAY;
                break;
            case PropertyType::RELATION:
                $type = PropertyType::INTEGER;
        }

        if (gettype($value) !== $type) {
            throw new PropertyValueInvalidException($key, $type, $value);
        }
    }
}
