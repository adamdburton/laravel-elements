<?php

namespace Click\Elements\Concerns\Element;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Element;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Exceptions\Relation\RelationNotDefinedException;
use Click\Elements\Exceptions\Relation\SingleRelationInvalidException;
use Click\Elements\Types\PropertyType;
use Click\Elements\Types\RelationType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

/**
 * Trait HasRelations
 * @method ElementDefinition getElementDefinition()
 */
trait HasRelations
{
    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @param $key
     * @param $value
     * @return HasRelations
     * @throws ManyRelationInvalidException
     * @throws SingleRelationInvalidException
     */
    public function setRelation($key, $value)
    {
        $propertyDefinition = $this->getElementDefinition()->getPropertyDefinition($key);
        $relationType = $this->getRelationType($key);

        $this->validateRelationProperty($propertyDefinition, $value);

        if ($relationType === RelationType::SINGLE) {
            $this->setSingleRelation($key, $value);
        } elseif ($relationType === RelationType::MANY) {
            $this->setManyRelations($key, $value);
        }

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getRelationType($key)
    {
        $propertyDefinition = $this->getElementDefinition()->getPropertyDefinition($key);

        return $propertyDefinition->getMeta('relationType');
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
        if (!is_array($value) && ! is_a($value, Collection::class)) {
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
     * @param $value
     */
    protected function setManyRelations($key, $value)
    {
        if ($value instanceof Collection) {
            $this->setManyRelationsFromArray($key, $value->all());
        } elseif (is_array($value)) {
            $isArrayOfElements = is_a(array_values($value)[0], Element::class);

            if ($isArrayOfElements) {
                $this->setManyRelationsFromArray($key, $value);
            } else {
                $this->setManyRelationsFromKeys($key, $value);
            }
        }
    }

    /**
     * @param $key
     * @param array $relations
     * @return void
     */
    protected function setManyRelationsFromArray($key, array $relations)
    {
        $this->attributes[$key] = array_map(function (Element $element) {
            return $element->getPrimaryKey();
        }, $relations);

        $this->relations[$key] = $relations;
    }

    /**
     * @param $key
     * @param array $keys
     * @return void
     */
    protected function setManyRelationsFromKeys($key, array $keys)
    {
        $this->attributes[$key] = $keys;
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
     * @return bool
     */
    public function hasRelationLoaded($key)
    {
        return isset($this->relations[$key]);
    }

    /**
     * @param $key
     * @return Element|Collection
     * @throws BindingResolutionException
     * @throws ElementNotRegisteredException
     * @throws RelationNotDefinedException
     */
    public function getRelation($key)
    {
        $relationType = $this->getRelationType($key);
        $propertyDefinition = $this->getElementDefinition()->getPropertyDefinition($key);

        if ($relationType === RelationType::SINGLE) {
            return $this->getSingleRelation($key, $propertyDefinition);
        } elseif ($relationType === RelationType::MANY) {
            return $this->getManyRelations($key, $propertyDefinition);
        }

        throw new RelationNotDefinedException($key, $this->getElementDefinition());
    }

    /**
     * @param string $key
     * @param PropertyDefinition $definition
     * @return Element
     */
    protected function getSingleRelation(string $key, PropertyDefinition $definition)
    {
        if (isset($this->relations[$key])) {
            return $this->relations[$key];
        }

        return null;
    }

    /**
     * @param string $key
     * @param PropertyDefinition $definition
     * @return \Click\Elements\Collection|null
     * @throws ElementNotRegisteredException
     * @throws BindingResolutionException
     */
    protected function getManyRelations(string $key, PropertyDefinition $definition)
    {
        if (isset($this->relations[$key])) {
            return new \Click\Elements\Collection($this->relations[$key]);
        }

        $primaryKeys = $this->attributes[$key];

        // TODO: Throw an Exception should this be called twice. Performance is essential.

        $elementType = $definition->getMeta('elementType');

        return elements()->getElementDefinition($elementType)->query()->findMany($primaryKeys);
    }
}
