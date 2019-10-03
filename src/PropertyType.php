<?php

namespace Click\Elements;

use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\InvalidPropertyValueException;

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
     * @param PropertyDefinition $definition
     * @param $value
     * @return void
     * @throws InvalidPropertyValueException
     */
    public static function validateValue(PropertyDefinition $definition, $value)
    {
        $type = $definition->getType();

        switch ($type) {
            case PropertyType::JSON:
                $type = PropertyType::ARRAY;
                break;
            case PropertyType::RELATION:
                $type = PropertyType::INTEGER;
        }

        if (gettype($value) !== $type) {
            dd(gettype($value), $type);
            throw new InvalidPropertyValueException($definition, $value);
        }
    }
}
