<?php

namespace Click\Elements;

use Click\Elements\Exceptions\ElementValidationFailed;
use Click\Elements\Models\Entity;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * Element query builder
 */
class Builder
{
    /** @var Element */
    protected $element;

    /** @var \Illuminate\Database\Eloquent\Builder */
    protected $query;

    /**
     * @param Element $element
     */
    public function __construct(Element $element)
    {
        $this->element = $element;

        $this->query = Entity::query()->with('properties');
    }

    /**
     * @param $property
     * @param string $operator
     * @param null $value
     * @return $this
     * @throws BindingResolutionException
     * @throws Exceptions\ElementTypeNotRegisteredException
     * @see Entity::scopeWhereHasProperty()
     */
    public function where($property, $operator = '', $value = null)
    {
        $property = $this->element->getElementDefinition()->getPropertyModel($property);

        $this->query->whereHasProperty($property, $operator, $value);

        return $this;
    }

    /**
     * @return Collection
     */
    public function get()
    {
        return $this->query->get();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->query->exists();
    }

    /**
     * @param null $attributes
     * @param null $id
     * @return Element
     * @throws BindingResolutionException
     * @throws Exceptions\ElementTypeNotRegisteredException
     */
    public function factory($attributes = null, $id = null)
    {
        return $this->element->getElementDefinition()->element($attributes, $id);
    }

    /**
     * @param array $attributes
     * @return mixed
     * @throws BindingResolutionException
     * @throws ElementValidationFailed
     * @throws Exceptions\ElementTypeNotRegisteredException
     * @throws Exceptions\PropertyMissingException
     */
    public function create(array $attributes)
    {
        $this->validate($attributes);

        $relations = $this->buildRelations(
            $this->element->getPropertyModels(),
            $attributes
        );

        /** @var Entity $entity */
        $entity = Entity::create();

        $entity->properties()->sync($relations);

        $element = $this->factory($attributes, $entity->id);

        return $element;
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws BindingResolutionException
     * @throws ElementValidationFailed
     * @throws Exceptions\ElementTypeNotRegisteredException
     * @throws Exceptions\PropertyMissingException
     */
    public function update(array $attributes)
    {
        $this->validate($attributes);

        $attributes = array_merge($this->element->getAttributes(), $attributes);

        $relations = $this->buildRelations(
            $this->element->getPropertyModels(),
            $attributes
        );

        /** @var Entity $entity */
        $entity = Entity::find($this->element->getPrimaryKey());

        $entity->properties()->sync($relations);

        $element = $this->factory($attributes);

        return $element;
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
     * @param array $attributes
     * @throws BindingResolutionException
     * @throws ElementValidationFailed
     * @throws Exceptions\ElementTypeNotRegisteredException
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
     * @throws Exceptions\ElementTypeNotRegisteredException
     * @throws BindingResolutionException
     */
    public function validateWith($attributes)
    {
        $rules = $this->element->getElementDefinition()->getValidationRules();

        $validator = Validator::make($attributes, $rules);

        return $validator;
    }
}
