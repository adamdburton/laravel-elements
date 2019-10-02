<?php

namespace Click\Elements;

use Click\Elements\Contracts\ElementContract;
use Click\Elements\Exceptions\ElementValidationFailed;
use Click\Elements\Models\Entity;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * Element query builder
 */
class Builder
{
    /**
     * @var Element
     */
    protected $element;

    /**
     * @var BaseBuilder
     */
    protected $query;

    /**
     * @param Element $element
     * @throws Exceptions\ElementNotRegisteredException
     * @throws Exceptions\ElementNotInstalledException
     */
    public function __construct(Element $element)
    {
        $this->element = $element;

        $this->query = Entity::query()->with('properties');

        // Add in the 'type' requirement

        $this->where('type', $this->element->getAlias());
    }

    /**
     * @param $property
     * @param string $operator
     * @param null $value
     * @return $this
     * @throws Exceptions\ElementNotRegisteredException
     * @throws Exceptions\ElementNotInstalledException
     * @see Entity::scopeWhereHasProperty()
     */
    public function where($property, $operator = '', $value = null)
    {
        $property = $this->element->getElementDefinition()->getPropertyModel($property);

        $this->query->whereHasProperty($property, $operator, $value);

        return $this;
    }

    /**
     * @return Element[]
     * @throws Exceptions\ElementNotRegisteredException
     */
    public function get()
    {
        return $this->mapIntoElements($this->query->get());
    }

    /**
     * @param $id
     * @return Element
     * @throws Exceptions\ElementNotRegisteredException
     */
    public function find($id)
    {
        return $this->mapIntoElement($this->query->find($id));
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->query->exists();
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws ElementValidationFailed
     * @throws Exceptions\ElementNotRegisteredException
     * @throws Exceptions\ElementNotInstalledException
     */
    public function create(array $attributes)
    {
        $this->validate($attributes);

        $relations = $this->buildRelations(
            $this->element->getElementDefinition()->getPropertyModels(),
            $attributes + ['type' => $this->element->getAlias()]
        );

        /** @var Entity $entity */
        $entity = Entity::create();

        $entity->properties()->sync($relations);

        $element = $this->factory($attributes, $entity->meta);

        return $element;
    }

    /**
     * @param array $attributes
     * @throws ElementValidationFailed
     * @throws Exceptions\ElementNotRegisteredException
     */
    protected function validate(array $attributes)
    {
        $validator = $this->validateWith($attributes);

        if ($failed = $validator->fails()) {
            throw new ElementValidationFailed($this->element, $validator);
        }
    }

    /**
     * @param $attributes
     * @return \Illuminate\Contracts\Validation\Validator
     * @throws Exceptions\ElementNotRegisteredException
     */
    public function validateWith($attributes)
    {
        $rules = $this->element->getElementDefinition()->getValidationRules();

        $validator = Validator::make($attributes, $rules);

        return $validator;
    }

    /**
     * @param array $properties [ Property({id: 1, name: eg1, type: int}), Property({id: 2, name: eg2, type: string}) ]
     * @param array $attributes [ eg1 => 1, eg2 => hello ]
     * @return array $relations [ 1 => [1 => [int_value => 1]], 2 => [string_value => hello] ]
     */
    protected function buildRelations(array $properties, array $attributes)
    {
        return collect($properties)->mapWithKeys(function ($property, $key) use ($attributes) {
            return isset($attributes[$key]) ? [$property->id => [$property->typeColumn => $attributes[$key]]] : [];
        })->filter()->all();
    }

    /**
     * @param null $attributes
     * @param null $id
     * @return Element
     * @throws Exceptions\ElementNotRegisteredException
     */
    public function factory($attributes = null, $id = null)
    {
        return $this->element->getElementDefinition()->factory($attributes, $id);
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws BindingResolutionException
     * @throws ElementValidationFailed
     * @throws Exceptions\ElementNotRegisteredException
     * @throws Exceptions\ElementNotInstalledException
     */
    public function update(array $attributes)
    {
        $this->validate($attributes);

        $attributes = array_merge($this->element->getAttributes(), $attributes);

        $relations = $this->buildRelations(
            $this->element->getElementDefinition()->getPropertyModels(),
            $attributes
        );

        /** @var Entity $entity */
        $entity = Entity::find($this->element->getPrimaryKey());

        $entity->properties()->sync($relations);

        $element = $this->factory($attributes, $entity->meta);

        return $element;
    }

    /**
     * @param Entity $model
     * @return Element
     * @throws Exceptions\ElementNotRegisteredException
     */
    protected function mapIntoElement(Entity $model)
    {
        return $model->toElement($this->element->getElementDefinition()->getClass());
    }

    /**
     * @param Collection $models
     * @return Element[]
     * @throws Exceptions\ElementNotRegisteredException
     */
    protected function mapIntoElements(Collection $models)
    {
        return $models->map->toElement($this->element->getElementDefinition()->getClass());
    }
}
