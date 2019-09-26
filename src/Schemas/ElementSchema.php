<?php

namespace Click\Elements\Schemas;

use Click\Elements\PropertyType;
use Click\Elements\Schema;

/**
 * Class ElementSchema
 */
class ElementSchema extends Schema
{
    /**
     * @param $key
     * @return PropertySchema
     */
    public function boolean($key)
    {
        return $this->schema[$key] = (new PropertySchema())->key($key)->type(PropertyType::BOOLEAN);
    }

    /**
     * @param $key
     * @return PropertySchema
     */
    public function integer($key)
    {
        return $this->schema[$key] = (new PropertySchema())->key($key)->type(PropertyType::INTEGER);
    }

    /**
     * @param $key
     * @return PropertySchema
     */
    public function double($key)
    {
        return $this->schema[$key] = (new PropertySchema())->key($key)->type(PropertyType::DOUBLE);
    }

    /**
     * @param $key
     * @return PropertySchema
     */
    public function string($key)
    {
        return $this->schema[$key] = (new PropertySchema())->key($key)->type(PropertyType::STRING);
    }

    /**
     * @param $key
     * @return PropertySchema
     */
    public function text($key)
    {
        return $this->schema[$key] = (new PropertySchema())->key($key)->type(PropertyType::TEXT);
    }

    /**
     * @param $key
     * @return PropertySchema
     */
    public function array($key)
    {
        return $this->schema[$key] = (new PropertySchema())->key($key)->type(PropertyType::ARRAY);
    }

    /**
     * @param $key
     * @return PropertySchema
     */
    public function json($key)
    {
        return $this->schema[$key] = (new PropertySchema())->key($key)->type(PropertyType::JSON);
    }

    /**
     * @param $key
     * @return PropertySchema
     */
    public function relation($key)
    {
        return $this->schema[$key] = (new PropertySchema())->key($key)->type(PropertyType::RELATION);
    }

    /**
     * @param $key
     * @return PropertySchema
     */
    public function timestamp($key)
    {
        return $this->schema[$key] = (new PropertySchema())->key($key)->type(PropertyType::TIMESTAMP);
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        $properties = collect($this->schema)->map(function (PropertySchema $schema) {
            return $schema->getSchema();
        })->all();

        return $properties;
    }
}
