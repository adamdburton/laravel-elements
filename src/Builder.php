<?php

namespace Click\Elements;

use Click\Elements\Concerns\Builder\InteractsWithModels;
use Click\Elements\Concerns\Builder\QueriesProperties;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotInstalledException;
use Click\Elements\Models\Entity;
use Closure;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

/**
 * Element query builder
 */
class Builder
{
    use QueriesProperties;
    use InteractsWithModels;

    /**
     * @var Element
     */
    protected $element;

    /**
     * @var Eloquent
     */
    protected $builder;

    /**
     * @var array
     */
    protected $withs = [];

    /**
     * @param Element $element
     */
    public function __construct(Element $element)
    {
        $this->element = $element;
    }

    /**
     * @param $name
     * @param $arguments
     * @return Builder|mixed
     */
    public function __call($name, $arguments)
    {
        if ($this->element->hasScope($name)) {
            $this->element->applyScope($name, $this->query(), $arguments);

            return $this;
        }

        if ($this->element->hasRelation($name)) {
            return $this->element->getRelationQuery($name);
        }
    }

    /**
     * @return Eloquent
     */
    public function query()
    {
        if (!$this->builder) {
            $this->builder = Entity::query()->where('type', $this->element->getAlias());
        }

        return $this->builder;
    }

    /**
     * @return ElementDefinition
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    public function getElementDefinition()
    {
        return elements()->getElementDefinition($this->element->getAlias());
    }

    /**
     * @param $withs
     * @return Builder
     */
    public function with($withs)
    {
        $this->withs = Arr::wrap($withs);

        return $this;
    }

    /**
     * @param string $property
     * @return Models\Property
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws PropertyNotInstalledException
     */
    protected function getPropertyModel(string $property)
    {
        return $this->getElementDefinition()->getPropertyModel($property);
    }

    /**
     * @param string $property
     * @return PropertyDefinition|null
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws Exceptions\Property\PropertyNotRegisteredException
     */
    protected function getPropertyDefinition(string $property)
    {
        return $this->getElementDefinition()->getPropertyDefinition($property);
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws Exceptions\Property\PropertyValidationFailed
     * @throws Exceptions\Property\PropertyValueInvalidException
     * @throws Exceptions\Relation\ManyRelationInvalidException
     * @throws Exceptions\Relation\SingleRelationInvalidException
     */
    public function create(array $attributes)
    {
        // Setting the attributes on an element will automatically
        // trigger type checking and validation.

        $this->element->setAttributes($attributes);

        // Continue, as an exception would have been throw by now.

        $entity = $this->createEntity($attributes);

        // Return a new Element crated from the entity.

        return $entity->toElement($this->getElementDefinition()->getAlias());
    }

    /**
     * Creates an element without type checking and validation
     *
     * @param array $attributes
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    public function createRaw(array $attributes)
    {
        $entity = $this->createEntity($attributes);

        return $entity->toElement($this->getElementDefinition()->getAlias());
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws Exceptions\Property\PropertyValidationFailed
     * @throws Exceptions\Property\PropertyValueInvalidException
     * @throws Exceptions\Relation\RelationElementTypeInvalidException
     */
    public function update(array $attributes)
    {
        // Setting the attributes on an element will automatically
        // trigger type checking and validation.

        $this->element->setAttributes($attributes);

        // Grab the original entity from the element being updated

        $entity = $this->element->getEntity();

        // Updated the entity

        $entity = $this->updateEntity($entity, $attributes);

        // Return a new Element composed from the updated entity

        return $entity->toElement($this->getElementDefinition()->getAlias());
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    public function updateRaw(array $attributes)
    {
        $entity = $this->element->getEntity();

        $this->updateEntity($entity, $attributes);

        return $entity->toElement($this->getElementDefinition()->getAlias());
    }

    /**
     * @param Collection $models
     * @return Element[]
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    protected function getWiths(Collection $models)
    {
        $properties = $this->getElementDefinition()->getPropertyDefinitions();

        return collect($this->withs)->map(function ($a, $b) use ($models, $properties) {
            $element = $a instanceof Closure ? $b : $a;
            $closure = $a instanceof Closure ? $a : null;

            $property = $properties[$element];
            $primaryKeys = $models->pluck($element);

            $query = elements()->getElementDefinition($property->getMeta('elementType'))->query();

            if ($closure) {
                $closure($query);
            }

            return $query->findMany($primaryKeys);
        })->flatten()->keyBy('meta.id')->all();
    }
}
