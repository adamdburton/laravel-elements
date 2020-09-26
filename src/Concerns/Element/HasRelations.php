<?php

namespace Click\Elements\Concerns\Element;

use Click\Elements\Builder;
use Click\Elements\Definitions\AttributeDefinition;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeNotDefinedException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Exceptions\Relation\RelationNotDefinedException;
use Click\Elements\Exceptions\Relation\SingleRelationInvalidException;
use Click\Elements\Types\AttributeType;
use Click\Elements\Types\RelationType;
use Illuminate\Support\Collection;

/**
 * Trait HasRelations
 * @method ElementDefinition getElementDefinition()
 * @method AttributeDefinition getAttributeDefinition($key)
 * @method Builder query()
 */
trait HasRelations
{
    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @param string|null $relation
     * @return mixed
     * @throws ElementNotRegisteredException
     */
    protected function relation(string $relation = null)
    {
        $relation = $relation ?? $this->findRelationName();

        return $this->query()->getRelationBuilder($relation);
    }

    /**
     * @return string
     */
    protected function findRelationName()
    {
        return debug_backtrace()[2]['function'];
    }

    /**
     * @param string $relation
     * @param $value
     * @return HasRelations
     * @throws ManyRelationInvalidException
     * @throws SingleRelationInvalidException
     * @throws AttributeNotDefinedException
     */
    public function setRelation(string $relation, $value)
    {
        $attributeDefinition = $this->getElementDefinition()->getAttributeDefinition($relation);
        $relationType = $this->getRelationType($relation);

        $this->validateRelationAttribute($attributeDefinition, $value);

        if ($relationType === RelationType::SINGLE) {
            $this->setSingleRelation($relation, $value);
        } elseif ($relationType === RelationType::MANY) {
            $this->setManyRelations($relation, $value);
        }

        return $this;
    }

    /**
     * @param string $relation
     * @return mixed
     * @throws AttributeNotDefinedException
     */
    public function getRelationType(string $relation)
    {
        $attributeDefinition = $this->getElementDefinition()->getAttributeDefinition($relation);

        return $attributeDefinition->getMeta('relationType');
    }

    /**
     * @param AttributeDefinition $definition
     * @param $value
     * @throws ManyRelationInvalidException
     * @throws SingleRelationInvalidException
     */
    protected function validateRelationAttribute(AttributeDefinition $definition, $value)
    {
        $relationType = $definition->getMeta('relationType');

        if ($relationType === RelationType::SINGLE) {
            $this->validateSingleRelationAttribute($definition, $value);
        } elseif ($relationType === RelationType::MANY) {
            $this->validateManyRelationAttribute($definition, $value);
        }

        // TODO: Throw exception
    }

    /**
     * @param AttributeDefinition $definition
     * @param $value
     * @throws SingleRelationInvalidException
     */
    protected function validateSingleRelationAttribute(AttributeDefinition $definition, $value)
    {
        $elementClass = $definition->getMeta('elementType');

        if (!$value instanceof $elementClass && !is_int($value)) {
            throw new SingleRelationInvalidException($definition->getKey(), $elementClass, $value);
        }
    }

    /**
     * @param AttributeDefinition $definition
     * @param $value
     * @throws ManyRelationInvalidException
     */
    protected function validateManyRelationAttribute(AttributeDefinition $definition, $value)
    {
        $elementClass = $definition->getMeta('elementType');

        if (!is_array($value) && !is_a($value, Collection::class)) {
            throw new ManyRelationInvalidException($definition->getKey(), $elementClass, $value);
        }

        foreach ($value as $item) {
            if (!$item instanceof $elementClass && !is_int($item)) {
                throw new ManyRelationInvalidException($definition->getKey(), $elementClass, $value);
            }
        }
    }

    /**
     * @param string $relation
     * @param $value
     */
    protected function setSingleRelation(string $relation, $value)
    {
        if ($value instanceof Element) {
            $this->relations[$relation] = $value;
            $this->attributes[$relation] = $value->getId();
        } else {
            $this->attributes[$relation] = $value;
        }
    }

    /**
     * @param string $relation
     * @param $value
     */
    protected function setManyRelations(string $relation, $value)
    {
        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (is_array($value) && count($value)) {
            $isArrayOfElements = is_a(array_values($value)[0], Element::class);

            if ($isArrayOfElements) {
                $this->setManyRelationsFromArray($relation, $value);
            } else {
                $this->setManyRelationsFromIds($relation, $value);
            }
        }

        // TODO: Throw exception
    }

    /**
     * @param string $relation
     * @param Element[] $relations
     * @return void
     */
    protected function setManyRelationsFromArray(string $relation, array $relations)
    {
        $this->attributes[$relation] = array_values(array_map(function (Element $element) {
            return $element->getId();
        }, $relations));

        $this->relations[$relation] = $relations;
    }

    /**
     * @param string $relation
     * @param int[] $ids
     * @return void
     */
    protected function setManyRelationsFromIds(string $relation, array $ids)
    {
        $this->attributes[$relation] = $ids;
    }

    /**
     * @param string $relation
     * @return bool
     */
    public function hasRelation(string $relation)
    {
        /** @var AttributeDefinition[] $attributes */
        $attributes = $this->getElementDefinition()->getAttributeDefinitions();

        if (!isset($attributes[$relation])) {
            return false;
        }

        $attribute = $attributes[$relation];

        return $attribute->getType() === AttributeType::RELATION;
    }

    /**
     * @param string relation
     * @return bool
     */
    public function hasRelationLoaded(string $relation)
    {
        return isset($this->relations[$relation]);
    }

    /**
     * @param string $relation
     * @return Element|Collection|null
     * @throws AttributeNotDefinedException
     */
    public function getLoadedRelation(string $relation)
    {
        $relationType = $this->getRelationType($relation);

        if ($relationType === RelationType::SINGLE) {
            return $this->relations[$relation] ?? null;
        } elseif ($relationType === RelationType::MANY) {
            return isset($this->relations[$relation]) ? new \Click\Elements\Collection($this->relations[$relation]) : null;
        }

        return null;
    }

    /**
     * @param string $relation
     * @return \Click\Elements\Collection|Element|null
     * @throws ElementNotRegisteredException
     * @throws RelationNotDefinedException
     * @throws AttributeNotDefinedException
     */
    public function getRelation(string $relation)
    {
        $relationType = $this->getRelationType($relation);

        if ($relationType === RelationType::SINGLE) {
            return $this->getSingleRelation($relation);
        } elseif ($relationType === RelationType::MANY) {
            return $this->getManyRelations($relation);
        } elseif ($relationType === RelationType::BELONGS_TO) {
            return $this->getManyBelongsToRelations($relation);
        }

        throw new RelationNotDefinedException($relation, $this->getElementDefinition());
    }

    /**
     * @param string $relation
     * @return Element
     * @throws ElementNotRegisteredException
     */
    protected function getSingleRelation(string $relation)
    {
        if (!isset($this->attributes[$relation])) {
            return null;
        }

        $elementType = $this->getAttributeDefinition($relation)->getMeta('elementType');

        return element($elementType)->find($this->attributes[$relation]);
    }

    /**
     * @param string $relation
     * @return \Click\Elements\Collection|null
     * @throws ElementNotRegisteredException
     */
    protected function getManyRelations(string $relation)
    {
        if (!isset($this->attributes[$relation])) {
            return null;
        }

        $elementType = $this->getAttributeDefinition($relation)->getMeta('elementType');
        $ids = $this->attributes[$relation];

        return element($elementType)->findMany($ids);
    }

    protected function getManyBelongsToRelations(string $relation)
    {

    }

    /**
     * @param string $relation
     */
    protected function unsetRelation(string $relation)
    {
        unset($this->relations[$relation]);
    }
}
