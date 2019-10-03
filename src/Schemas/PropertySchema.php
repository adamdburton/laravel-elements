<?php

namespace Click\Elements\Schemas;

use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Schema;

/**
 * Class ElementSchema
 */
class PropertySchema extends Schema
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param string $key
     * @param string $type
     * @throws PropertyKeyInvalidException
     */
    public function __construct(string $key, string $type)
    {
        $this->validateKey($key);

        $this->key = $key;
        $this->type = $type;
    }

    /**
     * @param $name
     * @param $arguments
     * @return Schema
     */
    public function __call($name, $arguments)
    {
        $this->meta[$name] = count($arguments) > 1 ? $arguments : ($arguments[0] ?? true);

        return $this;
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'meta' => $this->meta
        ];
    }
}
