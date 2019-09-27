<?php

namespace Click\Elements;

use Click\Elements\Contracts\SchemaContract;

/**
 * A blueprint-like interface for defining Element properties.
 */
abstract class Schema implements SchemaContract
{
    /**
     * @var array
     */
    protected $schema = [];

    /**
     * @param $name
     * @param $arguments
     * @return Schema
     */
    public function __call($name, $arguments)
    {
        $this->schema[$name] = $arguments[0] ?? true;

        return $this;
    }


}
