<?php

namespace Click\Elements;

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
            self::ARRAY,
            self::JSON,
            self::RELATION
        ];
    }
}
