<?php

namespace Click\Elements\Schemas;

use Click\Elements\Schema;

/**
 * Class AttributeSchema
 */
class AttributeSchema extends Schema
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
     */
    public function __construct(string $key, string $type)
    {
//        $this->validateKey($key); // TODO: Implement this?

        $this->key = $key;
        $this->type = $type;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $this->meta[$name] = count($arguments) > 1 ? $arguments : ($arguments[0] ?? true);

        return $this;
    }

    /**
     * @param $validation
     * @return $this
     */
    public function validation($validation)
    {
        $this->meta['validation'] = $validation;

        return $this;
    }

    /**
     * @return $this
     */
    public function unique()
    {
        $validation = $this->meta['validation'] ?? [];

        $validation[] = 'unique';

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
