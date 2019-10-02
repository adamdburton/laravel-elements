<?php

namespace Click\Elements;

use Click\Elements\Contracts\SchemaContract;
use Click\Elements\Exceptions\PropertyKeyInvalidException;

/**
 * A blueprint-like interface for defining Element properties.
 */
abstract class Schema implements SchemaContract
{
    protected $defaultSchema = [];

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

    /**
     * @param string $key
     * @throws PropertyKeyInvalidException
     */
    protected function validateKey(string $key)
    {
        if (in_array($key, $this->defaultSchema)) {
            throw new PropertyKeyInvalidException($key);
        }
    }
}
