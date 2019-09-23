<?php

namespace Click\Elements;

/**
 * Defines the available property types for elements.
 */
class PropertyType
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
     * @param $type
     * @return bool
     */
    public static function isValidType($type)
    {
        return in_array($type, self::getTypes());
    }

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
     * @param $type
     * @param $value
     * @return bool
     */
    public static function validateValue($type, $value)
    {
        switch ($type) {
            case PropertyType::ARRAY:
                $type = PropertyType::JSON;
                break;
            case PropertyType::RELATION:
                $type = PropertyType::INTEGER;
        }

        return gettype($value) === $type;
    }
}
