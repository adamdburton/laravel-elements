<?php

namespace Click\Elements\Concerns;

use Click\Elements\Exceptions\PropertyMissingException;
use Click\Elements\PropertyDefinition;
use Click\Elements\PropertyType;
use Illuminate\Support\Str;

/**
 * Provides typed properties for Elements
 */
trait HasTypedProperties
{
    /** @var array */
    protected $attributes = [];

    /** @var array */
    protected $properties = [];

    /**
     * @param $key
     * @return null
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * @param $key
     * @param $value
     * @throws PropertyMissingException
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute');
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasSetter($key)
    {
        return method_exists($this, 'set' . Str::studly($key) . 'Attribute');
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function runSetter($key, $value)
    {
        return $this->{'set' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     * @throws PropertyMissingException
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasSetter($key)) {
            $this->runSetter($key, $value);
        } else {
            $property = $this->getElementDefinition()->getPropertyDefinition($key);

            if (!$property) {
//            dd($key, $this->getElementDefinition());
                throw new PropertyMissingException($key);
            }

            $this->checkAttributeType($property, $value);

            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return collect($this->getAttributes())->map(function ($_, $key) {
            return $this->getAttribute($key);
        })->all();
    }

    /**
     * @param $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @param $attributes
     */
    public function setRawAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param PropertyDefinition $definition
     * @param $value
     * @return bool
     */
    protected function checkAttributeType(PropertyDefinition $definition, $value)
    {
        return PropertyType::validateValue($definition->getType(), $value);
    }
}
