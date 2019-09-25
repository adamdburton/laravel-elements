<?php

namespace Click\Elements\Schemas;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\PropertyType;

/**
 * Class ElementSchema
 */
class ElementSchema extends Schema
{
    /** @return string */
    public function getDefinitionClass()
    {
        return ElementDefinition::class;
    }

    /**
     * @param $key
     * @param $type
     * @return PropertyDefinition
     */
    protected function add($key, $type)
    {
        if (!isset($this->definition[$key])) {
            $schema = new PropertySchema();

            return $this->definition[$key] = $schema->type($type);
        }
    }

    /**
     * @param $key
     * @return PropertyDefinition
     */
    public function boolean($key)
    {
        return $this->add($key, PropertyType::BOOLEAN);
    }

    /**
     * @param $key
     * @return PropertyDefinition
     */
    public function integer($key)
    {
        return $this->add($key, PropertyType::INTEGER);
    }

    /**
     * @param $key
     * @return PropertyDefinition
     */
    public function double($key)
    {
        return $this->add($key, PropertyType::DOUBLE);
    }

    /**
     * @param $key
     * @return PropertyDefinition
     */
    public function string($key)
    {
        return $this->add($key, PropertyType::STRING);
    }

    /**
     * @param $key
     * @return PropertyDefinition
     */
    public function text($key)
    {
        return $this->add($key, PropertyType::TEXT);
    }

    /**
     * @param $key
     * @return PropertyDefinition
     */
    public function array($key)
    {
        return $this->add($key, PropertyType::ARRAY);
    }

    /**
     * @param $key
     * @return PropertyDefinition
     */
    public function json($key)
    {
        return $this->add($key, PropertyType::JSON);
    }

    /**
     * @param $key
     * @return PropertyDefinition
     */
    public function relation($key)
    {
        return $this->add($key, PropertyType::RELATION);
    }

    /**
     * @param $key
     * @return PropertyDefinition
     */
    public function timestamp($key)
    {
        return $this->add($key, PropertyType::TIMESTAMP);
    }
}