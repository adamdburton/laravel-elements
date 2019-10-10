<?php

namespace Click\Elements\Concerns\Element;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Element;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Exceptions\Property\PropertyNotRegisteredException;
use Click\Elements\Exceptions\Property\PropertyValidationFailed;
use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Exceptions\Relation\SingleRelationInvalidException;
use Click\Elements\Types\PropertyType;
use Click\Elements\Types\RelationType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Provides typed properties for Elements
 * @method ElementDefinition getElementDefinition()
 * @property Element $element;
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
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     * @throws PropertyKeyInvalidException
     * @throws PropertyNotRegisteredException
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * @param $key
     * @param $value
     * @throws ManyRelationInvalidException
     * @throws PropertyValidationFailed
     * @throws PropertyValueInvalidException
     * @throws SingleRelationInvalidException
     * @throws PropertyNotRegisteredException
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * @param $key
     * @return mixed
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     * @throws PropertyKeyInvalidException
     * @throws PropertyNotRegisteredException
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes) ||
            $this->hasGetMutator($key) ||
            $this->hasLoadedRelation($key) ||
            $this->hasRelation($key)) {
            return $this->getAttributeValue($key);
        }

        throw new PropertyKeyInvalidException($key);
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
     * @param $key
     * @return bool
     */
    protected function hasLoadedRelation($key)
    {
        return isset($this->relations[$key]);
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
     * @param string $key
     * @return mixed
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     * @throws PropertyNotRegisteredException
     */
    public function getAttributeValue($key)
    {
        $value = $this->attributes[$key] ?? null;

        if ($this->hasRelation($key)) {
            // TODO: Throw an Exception should this be called twice. Performance is essential.

            return $this->getRelation($key);
        }

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    /**
     * @param $key
     * @return Element|Collection
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     * @throws PropertyNotRegisteredException
     */
    protected function getRelation($key)
    {
        $propertyDefinition = $this->getElementDefinition()->getPropertyDefinition($key);
        $relationType = $propertyDefinition->getMeta('relationType');

        if ($relationType === RelationType::SINGLE) {
            return $this->getSingleRelation($key, $propertyDefinition);
        } elseif ($relationType === RelationType::MANY) {
            return $this->getManyRelations($key, $propertyDefinition);
        }
    }

    /**
     * @param string $key
     * @param PropertyDefinition $definition
     * @return Element
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     */
    protected function getSingleRelation(string $key, PropertyDefinition $definition)
    {
        if (isset($this->relations[$key])) {
            return $this->relations[$key];
        }

        $primaryKey = $this->attributes[$key];

        if (!$primaryKey) {
            return null;
        }

        $elementType = $definition->getMeta('elementType');

        return elements()->getElementDefinition($elementType)->query()->find($primaryKey);
    }

    /**
     * @param string $key
     * @param PropertyDefinition $definition
     * @return Element[]|null
     * @throws ElementNotRegisteredException
     * @throws BindingResolutionException
     */
    protected function getManyRelations(string $key, PropertyDefinition $definition)
    {
        if (isset($this->relations[$key])) {
            return $this->relations[$key];
        }

        $primaryKeys = $this->attributes[$key];

        if (!$primaryKeys || !is_array($primaryKeys)) {
            return null;
        }

        $elementType = $definition->getMeta('elementType');

        return elements()->getElementDefinition($elementType)->query()->findMany($primaryKeys);
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
     * @throws ManyRelationInvalidException
     * @throws PropertyValidationFailed
     * @throws PropertyValueInvalidException
     * @throws SingleRelationInvalidException
     * @throws PropertyNotRegisteredException
     * @thr«ows PropertyNotRegisteredException
     */
    public function setAttribute($key, $value)
    {
        $this->validateAttribute($key, $value);

        if ($this->hasRelation($key)) {
            $this->setRelation($key, $value);
        } elseif ($this->hasSetter($key)) {
            $this->runSetter($key, $value);
        } else {
            $this->setAttributeValue($key, $value);
        }

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @throws PropertyValidationFailed
     */
    protected function validateAttribute($key, $value)
    {
        $rules = $this->getElementDefinition()->getValidationRules();

        if (!isset($rules[$key])) {
            return;
        }

        // TODO: Allow passing validation messages and custom attributes here

        $validator = Validator::make(['value' => $value], ['value' => $rules[$key]]);

        if ($validator->fails()) {
            throw new PropertyValidationFailed($this->getAlias(), $key, $validator->getMessageBag()->get('value'));
        }
    }

    /**
     * @param $key
     * @param $value
     * @return HasTypedProperties
     * @throws ManyRelationInvalidException
     * @throws SingleRelationInvalidException
     * @throws PropertyNotRegisteredException
     */
    public function setRelation($key, $value)
    {
        /** @var PropertyDefinition $propertyDefinition */
        $propertyDefinition = $this->getElementDefinition()->getPropertyDefinition($key);
        $relationType = $propertyDefinition->getMeta('relationType');

        $this->validateRelationProperty($propertyDefinition, $value);

        if ($relationType === RelationType::SINGLE) {
            $this->setSingleRelation($key, $value);
        } elseif ($relationType === RelationType::MANY) {
            $this->setManyRelations($key, $value);
        }

//        $this->relations[$key] = $value;
//
//        $this->attributes[$key] = $value->getPrimaryKey();

        return $this;
    }

    /**
     * @param PropertyDefinition $definition
     * @param $value
     * @throws ManyRelationInvalidException
     * @throws SingleRelationInvalidException
     */
    protected function validateRelationProperty(PropertyDefinition $definition, $value)
    {
        $relationType = $definition->getMeta('relationType');
        $elementType = $definition->getMeta('elementType');

        if ($relationType === RelationType::SINGLE) {
            $this->validateSingleRelationProperty($definition, $elementType, $value);
        } elseif ($relationType === RelationType::MANY) {
            $this->validateManyRelationProperty($definition, $elementType, $value);
        }
    }

    /**
     * @param PropertyDefinition $definition
     * @param $value
     * @param string $elementClass
     * @throws SingleRelationInvalidException
     */
    protected function validateSingleRelationProperty(PropertyDefinition $definition, string $elementClass, $value)
    {
        if (!$value instanceof $elementClass && !is_int($value)) {
            throw new SingleRelationInvalidException($definition->getKey(), $elementClass, $value);
        }
    }

    /**
     * @param PropertyDefinition $definition
     * @param string $elementClass
     * @param $value
     * @throws ManyRelationInvalidException
     */
    protected function validateManyRelationProperty(PropertyDefinition $definition, string $elementClass, $value)
    {
        if (!is_array($value)) {
            throw new ManyRelationInvalidException($definition->getKey(), $elementClass, $value);
        }

        foreach ($value as $item) {
            if (!$item instanceof $elementClass && !is_int($item)) {
                throw new ManyRelationInvalidException($definition->getKey(), $elementClass, $value);
            }
        }
    }

    /**
     * @param $key
     * @param $value
     */
    protected function setSingleRelation($key, $value)
    {
        if ($value instanceof Element) {
            $this->relations[$key] = $value;
            $this->attributes[$key] = $value->getPrimaryKey();
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * @param $key
     * @param array $value
     */
    protected function setManyRelations($key, array $value)
    {
        if ($value[0] instanceof Element) {
            $this->relations[$key] = $value;
            $this->attributes[$key] = collect($value)->map->getPrimaryKey()->all();
        } else {
            $this->attributes[$key] = $value;
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
     * @param $key
     * @param $value
     * @return HasTypedProperties
     * @throws PropertyValidationFailed
     * @throws PropertyValueInvalidException
     * @throws PropertyNotRegisteredException
     */
    public function setAttributeValue($key, $value)
    {
        $this->validatePropertyValue($key, $value);

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @throws PropertyValidationFailed
     * @throws PropertyValueInvalidException
     * @throws PropertyNotRegisteredException
     */
    public function validatePropertyValue(string $key, $value)
    {
        /** @var PropertyDefinition $property */
        $definition = $this->getElementDefinition()->getPropertyDefinition($key);

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

        // TODO: Fix single value validation being the below because blergh.

        $validator = Validator::make(['value' => $value], ['value' => $rules]);

        if ($validator->fails()) {
            throw new PropertyValidationFailed($definition->getAlias(), $validator);
        }
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return collect($this->attributes)->mapWithKeys(function ($key) {
            return [$key => $this->getAttribute($key)];
        })->all();
    }

    /**
     * @param $attributes
     * @return $this
     * @throws ManyRelationInvalidException
     * @throws PropertyValidationFailed
     * @throws PropertyValueInvalidException
     * @throws SingleRelationInvalidException
     * @throws PropertyNotRegisteredException
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRawAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param $attributes
     */
    public function setRawAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
}
