<?php

namespace Click\Elements;

use Click\Elements\Contracts\TypeContract;

/**
 * Class Type
 */
abstract class Type implements TypeContract
{
    /**
     * @return array
     */
    abstract public static function getTypes();
}
