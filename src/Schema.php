<?php

namespace Click\Elements;

use Click\Elements\Elements\TypedProperty;
use Click\Elements\Exceptions\SchemaPropertyAlreadyDefined;

/**
 * A blueprint-like interface for defining Element properties.
 */
class Schema
{
    /** @var array */
    protected $definition = [];

    /** @var ElementDefinition */
    protected $elementType;

    /**
     * @param ElementDefinition $elementType
     */
    public function __construct(ElementDefinition $elementType)
    {
        $this->elementType = $elementType;
    }

    /**
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param $key
     * @param $type
     * @return TypedProperty
     * @throws SchemaPropertyAlreadyDefined
     */
    protected function add($key, $type)
    {
        if (isset($this->definition[$key])) {
            throw new SchemaPropertyAlreadyDefined($key, $this->elementType);
        }

        return $this->definition[$key] = tap(new TypedProperty(), function ($property) use ($key, $type) {
            /** @var TypedProperty $property */
            $property->setRawAttributes([
                'key' => $this->elementType->getType() . '.' . $key,
                'type' => $type
            ]);
        });
    }

    /**
     * @param $key
     * @return TypedProperty
     */
    public function boolean($key)
    {
        return $this->add($key, PropertyType::BOOLEAN);
    }

    /**
     * @param $key
     * @return TypedProperty
     */
    public function integer($key)
    {
        return $this->add($key, PropertyType::INTEGER);
    }

    /**
     * @param $key
     * @return TypedProperty
     */
    public function double($key)
    {
        return $this->add($key, PropertyType::DOUBLE);
    }

    /**
     * @param $key
     * @return TypedProperty
     */
    public function string($key)
    {
        return $this->add($key, PropertyType::STRING);
    }

    /**
     * @param $key
     * @return TypedProperty
     */
    public function text($key)
    {
        return $this->add($key, PropertyType::TEXT);
    }

    /**
     * @param $key
     * @return TypedProperty
     */
    public function array($key)
    {
        return $this->add($key, PropertyType::ARRAY);
    }

    /**
     * @param $key
     * @return TypedProperty
     */
    public function json($key)
    {
        return $this->add($key, PropertyType::JSON);
    }

    /**
     * @param $key
     * @return TypedProperty
     */
    public function relation($key)
    {
        return $this->add($key, PropertyType::RELATION);
    }

    /**
     * @param $key
     * @return TypedProperty
     */
    public function timestamp($key)
    {
        return $this->add($key, PropertyType::TIMESTAMP);
    }
}
