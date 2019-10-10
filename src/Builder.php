<?php

namespace Click\Elements;

use BadMethodCallException;
use Click\Elements\Concerns\Builder\InteractsWithModels;
use Click\Elements\Concerns\Builder\QueriesProperties;
use Click\Elements\Concerns\Builder\QueriesRelatedElements;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException as ElementNotRegisteredExceptionAlias;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Exceptions\ElementsNotInstalledException as ElementsNotInstalledExceptionAlias;
use Click\Elements\Exceptions\Property\PropertyNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotRegisteredException;
use Click\Elements\Models\Entity;
use Closure;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Element query builder
 */
class Builder
{
    use QueriesProperties;
    use QueriesRelatedElements;
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
     * @throws ElementNotRegisteredExceptionAlias
     * @throws ElementsNotInstalledExceptionAlias
     * @throws PropertyNotRegisteredException
     */
    public function __call($name, $arguments)
    {
        if ($this->element->hasScope($name)) {
            $this->element->applyScope($name, $this->query(), $arguments);

            return $this;
        }

        if ($this->element->hasRelation($name)) {
            return $this->getRelationQuery($name);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', static::class, $name));
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->query()->exists();
    }

    /**
     * @return Element|null
     */
    public function first()
    {
        $element = $this->query()->first();

        if (!$element) {
            return null;
        }

        return $this->mapIntoElement($element);
    }

    /**
     * @return string
     */
    public function toSql()
    {
        return $this->query()->toSql();
    }

    /**
     * @return \Click\Elements\Collection
     */
    public function get()
    {
        return $this->mapIntoElements($this->query()->get());
    }

    /**
     * @param $primaryKey
     * @return Element
     */
    public function find($primaryKey)
    {
        return $this->mapIntoElement($this->query()->find($primaryKey));
    }

    /**
     * @param Model $element
     * @return Element
     */
    protected function mapIntoElement(Model $element)
    {
        return $element->toElement();
    }

    /**
     * @param $primaryKeys
     * @return \Click\Elements\Collection
     */
    public function findMany($primaryKeys)
    {
        return $this->mapIntoElements($this->query()->findMany($primaryKeys));
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
     * @param string $relation
     * @return Builder
     * @throws ElementNotRegisteredExceptionAlias
     * @throws ElementsNotInstalledExceptionAlias
     * @throws PropertyNotRegisteredException
     */
    public function getRelationQuery(string $relation)
    {
        $propertyDefinition = $this->getPropertyDefinition($relation);

        $elementType = $propertyDefinition->getMeta('elementType');
        $elementDefinition = elements()->getElementDefinition($elementType);

        $element = $elementDefinition->factory();

        return $element->query();
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
     * @param array $attributes
     * @return Element
     * @throws Exceptions\Property\PropertyValidationFailed
     * @throws Exceptions\Property\PropertyValueInvalidException
     * @throws Exceptions\Relation\ManyRelationInvalidException
     * @throws Exceptions\Relation\SingleRelationInvalidException
     * @throws ElementNotInstalledException
     * @throws Exceptions\Property\PropertyNotRegisteredException
     */
    public function create(array $attributes)
    {
        // Setting the attributes on an element will automatically
        // trigger type checking and validation.

        $this->element->setAttributes($attributes);

        // Continue, as an exception would have been throw by now.

        $entity = $this->createEntity($this->element->getRawAttributes());

        // Return a new Element crated from the entity.

        return $this->element->setMeta($entity->getMeta());
    }

    /**
     * Creates an element without type checking and validation
     *
     * @param array $attributes
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws ElementNotInstalledException
     */
    public function createRaw(array $attributes)
    {
        $entity = $this->createEntity($attributes);

        return $entity->toElement();
    }

    /**
     * @param array $attributes
     * @return void
     * @throws Exceptions\Property\PropertyValidationFailed
     * @throws Exceptions\Property\PropertyValueInvalidException
     * @throws Exceptions\Relation\ManyRelationInvalidException
     * @throws Exceptions\Relation\SingleRelationInvalidException
     * @throws ElementNotInstalledException
     * @throws PropertyNotRegisteredException
     */
    public function update(array $attributes)
    {
        // Setting the attributes on an element will automatically
        // trigger type checking and validation.

        $this->element->setAttributes($attributes);

        // Grab the original entity from the element being updated

        $entity = $this->element->getEntity();

        // Updated the entity

        $this->updateEntity($entity, $attributes);

        // Return a new Element composed from the updated entity

        $this->element;
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws ElementNotInstalledException
     */
    public function updateRaw(array $attributes)
    {
        $entity = $this->element->getEntity();

        $this->updateEntity($entity, $attributes);

        return $entity->toElement();
    }

    /**
     * @param Eloquent $query
     * @return Builder
     */
    public function setQuery(Eloquent $query)
    {
        $this->builder = $query;

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
     * @param EloquentCollection $models
     * @return Element[]
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    protected function getWiths(EloquentCollection $models)
    {
        $properties = $this->getElementDefinition()->getPropertyDefinitions();

        return collect($this->withs)->map(function ($a, $b) use ($models, $properties) {
            $key = $a instanceof Closure ? $b : $a;
            $callback = $a instanceof Closure ? $a : null;

            $property = $properties[$key];
            $primaryKeys = $models->pluck($key);

            $query = elements()->getElementDefinition($property->getMeta('elementType'))->query();

            if ($callback) {
                $callback($query);
            }

            return $query->findMany($primaryKeys);
        })->flatten()->keyBy('meta.id')->all();
    }
}
