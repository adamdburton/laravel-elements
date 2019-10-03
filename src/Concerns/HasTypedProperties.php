<?php

namespace Click\Elements\Concerns;

use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\PropertyValueInvalidException;
use Click\Elements\Exceptions\PropertyNotRegisteredException;
use Click\Elements\Types\PropertyType;
use Illuminate\Support\Str;

/**
 * Provides typed properties for Elements
 */
trait HasTypedProperties
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
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
     * @throws PropertyNotRegisteredException
     * @throws PropertyValueInvalidException
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
     * @param $value
     * @return $this
     * @throws PropertyNotRegisteredException
     * @throws PropertyValueInvalidException
     * @thr«ows PropertyNotRegisteredException
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasSetter($key)) {
            $this->runSetter($key, $value);
        } else {
            /** @var PropertyDefinition $property */
            $property = $this->getElementDefinition()->getPropertyDefinition($key);

            if (!$property) {
                throw new PropertyNotRegisteredException($key);
            }

            PropertyType::validateValue($property->getKey(), $property->getType(), $value);

            $this->attributes[$key] = $value;
        }

        return $this;
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
     * @return array
     */
    public function toArray()
    {
        return collect($this->getAttributes())->keys()->map(function ($key) {
            return $this->getAttribute($key);
        })->all();
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param $attributes
     * @return $this
     * @throws PropertyNotRegisteredException
     * @throws PropertyValueInvalidException
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
}
