<?php

namespace Click\Elements\Schemas;

use Click\Elements\Exceptions\PropertyKeyInvalidException;
use Click\Elements\PropertyType;
use Click\Elements\Schema;

/**
 * Class ElementSchema
 */
class ElementSchema extends Schema
{
    /**
     * @var PropertySchema[]
     */
    protected $schema = [];

    public function __construct()
    {
        $this->add('type', PropertyType::STRING)->label('Element Type');
    }

    /**
     * @param $key
     * @param $type
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    protected function add($key, $type)
    {
        return $this->schema[$key] = new PropertySchema($key, $type);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function boolean($key)
    {
        return $this->add($key, PropertyType::BOOLEAN);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function integer($key)
    {
        return $this->add($key, PropertyType::INTEGER);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function double($key)
    {
        return $this->add($key, PropertyType::DOUBLE);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function string($key)
    {
        return $this->add($key, PropertyType::STRING);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function text($key)
    {
        return $this->add($key, PropertyType::TEXT);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function array($key)
    {
        return $this->add($key, PropertyType::ARRAY);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function json($key)
    {
        return $this->add($key, PropertyType::JSON);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function relation($key)
    {
        return $this->add($key, PropertyType::RELATION);
    }

    /**
     * @param $key
     * @return PropertySchema
     * @throws PropertyKeyInvalidException
     */
    public function timestamp($key)
    {
        return $this->add($key, PropertyType::TIMESTAMP);
    }

    /**
     * @return PropertySchema[]
     */
    public function getSchema()
    {
        return $this->schema;
    }
}
