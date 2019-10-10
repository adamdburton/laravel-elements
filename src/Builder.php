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
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotRegisteredException;
use Click\Elements\Models\Entity;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws PropertyNotRegisteredException
     */
    public function __call($name, $arguments)
    {
        if ($this->element->hasScope($name) ||
            $this->element->hasRelation($name)) {
            return $this->applyCallback($name, $arguments);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', static::class, $name));
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this|Builder
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws PropertyNotRegisteredException
     */
    protected function applyCallback($name, $arguments)
    {
        if ($this->element->hasScope($name)) {
            $this->element->applyScope($name, $this, $arguments);
        }

        if ($this->element->hasRelation($name)) {
            return $this->getRelationQuery($name);
        }

        return $this;
    }

    /**
     * @param string $relation
     * @return Builder
     * @throws ElementNotRegisteredException
     * @throws PropertyNotRegisteredException
     * @throws BindingResolutionException
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
     * @throws PropertyNotRegisteredException
     * @throws BindingResolutionException
     */
    protected function getPropertyDefinition(string $property)
    {
        return $this->getElementDefinition()->getPropertyDefinition($property);
    }

    /**
     * @return ElementDefinition
     * @throws ElementNotRegisteredException
     * @throws BindingResolutionException
     */
    public function getElementDefinition()
    {
        return elements()->getElementDefinition($this->element->getAlias());
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->query()->exists();
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
     * @return Collection
     */
    public function all()
    {
        return $this->element->newQuery()->get();
    }

    /**
     * @return Collection
     */
    public function get()
    {
        return $this->mapIntoElements($this->query()->get());
    }

    /**
     * @return string
     */
    public function toSql()
    {
        return $this->query()->toSql();
    }

    /**
     * @param $primaryKey
     * @return Element
     */
    public function find($primaryKey)
    {
        $this->query()->whereKey($primaryKey);

        return $this->first();
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
     * @throws ElementNotInstalledException
     * @throws PropertyNotRegisteredException
     */
    public function createRaw(array $attributes)
    {
        $entity = $this->createEntity($attributes);

        return $entity->toElement();
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws ElementNotInstalledException
     * @throws Exceptions\Property\PropertyValidationFailed
     * @throws Exceptions\Property\PropertyValueInvalidException
     * @throws Exceptions\Relation\ManyRelationInvalidException
     * @throws Exceptions\Relation\SingleRelationInvalidException
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

        $this->updateEntity($entity, $this->element->getRawAttributes());

        // Return a new Element composed from the updated entity

        return $this->element->setMeta($entity->getMeta());
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementNotInstalledException
     * @throws PropertyNotRegisteredException
     */
    public function updateRaw(array $attributes)
    {
        $this->element->setRawAttributes($attributes);

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
     * @throws BindingResolutionException
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     * @throws PropertyNotInstalledException
     */
    protected function getPropertyModel(string $property)
    {
        return $this->getElementDefinition()->getPropertyModel($property);
    }

    /**
     * @param Collection $models
     * @return Element[]
     * @throws BindingResolutionException
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     */
    protected function getWiths(Collection $models)
    {
        $propertyDefinitions = $this->getElementDefinition()->getPropertyDefinitions();
        $propertyModels = $this->getElementDefinition()->getPropertyModels();

        return collect($this->withs)->map(function ($a, $b) use ($models, $propertyDefinitions, $propertyModels) {
            $key = $a instanceof Closure ? $b : $a;
            $callback = $a instanceof Closure ? $a : null;

            $propertyDefinition = $propertyDefinitions[$key];
            $propertyModel = $propertyModels[$key];

            $primaryKeys = $models->map(function ($model) use ($propertyModel) {
                return $model->properties->pluck('pivot.' . $propertyModel->getPivotColumnKey());
            })->flatten()->all();

            $query = elements()->getElementDefinition($propertyDefinition->getMeta('elementType'))->query();

            if ($callback) {
                $callback($query);
            }

            return $query->findMany($primaryKeys);
        })->flatten()->keyBy('meta.id')->all();
    }

    /**
     * @param $primaryKeys
     * @return Collection
     */
    public function findMany($primaryKeys)
    {
        return $this->mapIntoElements($this->query()->findMany($primaryKeys));
    }
}
