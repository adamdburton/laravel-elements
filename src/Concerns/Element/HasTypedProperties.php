<?php

namespace Click\Elements\Concerns\Element;

use Click\Elements\Builder;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\Element\ElementValidationFailed;
use Click\Elements\Exceptions\Property\PropertyNotRegisteredException;
use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Click\Elements\Types\PropertyType;
use Illuminate\Support\Facades\Validator;
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
     * @var array
     */
    protected $relations = [];

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

//    /**
//     * @param $name
//     * @param $arguments
//     */
//    public function __call($name, $arguments)
//    {
//        /** @var PropertyDefinition[] $properties */
//        $properties = $this->getElementDefinition()->getProperties();
//
//        if (isset($properties[$name])) {
//            $property = $properties[$name];
//
//            if($property->getType() === PropertyType::RELATION) {
//                return elements()->getElementDefinition($property->getMeta('elementType'));
//            }
//        }
//    }

    /**
     * @param $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key) || $this->hasLoadedRelation($key)) {
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
        $value = $this->attributes[$key] ?? null;

        if ($this->hasLoadedRelation($key)) {
            return $this->getLoadedRelation($key);
        }

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        $property = $this->getElementDefinition()->getProperty($key);

        return $value;
    }

    /**
     * @param $key
     * @return bool
     */
    protected function isRelation($key)
    {
        /** @var PropertyDefinition $property */
        $property = $this->getElementDefinition()->getProperty($key);

        return $property->getType() === PropertyType::RELATION;
    }

    /**
     * @param $key
     * @return bool
     */
    protected function hasLoadedRelation($key)
    {
        return isset($this->relations[$key]);
    }

    /**
     * @param $key
     * @return null
     */
    protected function getLoadedRelation($key)
    {
        return $this->relations[$key] ?? null;
    }

    /**
     * @param $relations
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;
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
        if($this->hasRelation($key)) {
            $this->setRelation($key, $value);
        } elseif ($this->hasSetter($key)) {
            $this->runSetter($key, $value);
        } else {
            /** @var PropertyDefinition $property */
            $property = $this->getElementDefinition()->getProperty($key);

            if (!$property) {
                throw new PropertyNotRegisteredException($key);
            }

            $this->validatePropertyValue($property, $value);

            if ($property->getType() === PropertyType::RELATION) {
                $this->relations[$key] = $value;
            } else {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * @param PropertyDefinition $definition
     * @param $value
     * @throws PropertyValueInvalidException
     * @throws ElementValidationFailed
     */
    public function validatePropertyValue(PropertyDefinition $definition, $value)
    {
        switch ($type = $definition->getType()) {
            case PropertyType::JSON:
                $type = PropertyType::ARRAY;
                break;
            case PropertyType::RELATION:
                $type = PropertyType::UNSIGNED_INTEGER;
                break;
        }

        // Check the types first

        if (gettype($value) !== $type) {
            throw new PropertyValueInvalidException($definition->getKey(), $type, $value);
        }

        // Then validation

        $rules = $this->getElementDefinition()->getValidationRules();

        $validator = Validator::make(['value' => $value], ['value' => $rules]);

        if ($validator->fails()) {
            throw new ElementValidationFailed($definition->getElementDefinition()->getAlias(), $validator);
        }
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
    public function getAttributes()
    {
        return collect($this->properties)->keys()->map(function ($key) {
            return $this->getAttribute($key);
        })->all();
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

    /**
     * @param $key
     * @return bool
     */
    public function hasRelation($key)
    {
        /** @var PropertyDefinition[] $properties */
        $properties = $this->getElementDefinition()->getPropertyDefinitions();

        if (!isset($properties[$key])) {
            return false;
        }

        $property = $properties[$key];

        return $property->getType() === PropertyType::RELATION;
    }

    /**
     * @param $key
     * @return Builder
     */
    public function getRelationQuery($key)
    {
        return $this->getElementDefinition()->getPropertyDefinition($key)->factory()->query();
    }
}
