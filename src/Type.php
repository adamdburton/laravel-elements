<?php

namespace Click\Elements;

use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\PropertyValueInvalidException;
use Click\Elements\Exceptions\PropertyTypeInvalidException;

/**
 * Class Type
 */
abstract class Type
{
    /**
     * @param string $key
     * @param string $type
     * @param $value
     * @return mixed
     */
    abstract public static function validateValue(string $key, string $type, $value);

    /**
     * @return array
     */
    abstract public static function getTypes();

}
