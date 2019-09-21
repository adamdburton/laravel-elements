<?php

namespace Click\Elements\Concerns;

use Click\Elements\Models\Property;
use Click\Elements\PropertyType;

trait HasTypedAttributes
{
    protected $attributes = [];

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute($key, $value)
    {
        if ($property = $this->getElementType()->getProperty($key)) {
            $this->checkAttributeType($property, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @param Property $property
     * @param $value
     * @return bool
     */
    protected function checkAttributeType(Property $property, $value)
    {
//        return PropertyType::validateValue($property, $value);
    }
}
