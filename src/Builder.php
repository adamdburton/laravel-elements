<?php

namespace Click\Elements;

use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Element\ElementValidationFailed;
use Click\Elements\Exceptions\Property\PropertyNotInstalledException;
use Click\Elements\Models\Entity;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
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
     * @throws ElementNotRegisteredException
     * @throws ElementNotInstalledException
     * @throws Exceptions\ElementsNotInstalledException
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
     * @throws ElementNotRegisteredException
     * @throws ElementNotInstalledException
     * @throws Exceptions\ElementsNotInstalledException
     * @see Entity::scopeWhereHasProperty()
     */
    public function where($property, $operator = '', $value = null)
    {
        try {
            $property = $this->element->getElementDefinition()->getPropertyModel($property);
        } catch (PropertyNotInstalledException $e) {
            throw new ElementNotInstalledException($this->element->getAlias());
        }

        $this->query->whereHasProperty($property, $operator, $value);

        return $this;
    }

    /**
     * @param Request $request
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
     */
    public function applyRequest(Request $request)
    {
        $params = $request->all();

        // Search properties

        $properties = $this->element->getElementDefinition()->getProperties();

        collect($properties)->each(function (PropertyDefinition $property) use ($params) {
            if (isset($params[$key = $property->getKey()])) {
                $this->where($key, $params[$key]);
            }
        });
    }

    /**
     * @return Element[]
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
     */
    public function get()
    {
        return $this->mapIntoElements($this->query->get());
    }

    /**
     * @param Collection $models
     * @return Element[]
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
     */
    protected function mapIntoElements(Collection $models)
    {
        return $models->map->toElement($this->element->getElementDefinition()->getClass());
    }

    /**
     * @param $id
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
     */
    public function find($id)
    {
        return $this->mapIntoElement($this->query->find($id));
    }

    /**
     * @param Entity $model
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
     */
    protected function mapIntoElement(Entity $model)
    {
        return $model->toElement($this->element->getElementDefinition()->getClass());
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
     * @throws ElementNotRegisteredException
     * @throws ElementNotInstalledException
     * @throws Exceptions\ElementsNotInstalledException
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

        return $entity->toElement($this->element->getAlias());
    }

    /**
     * @param array $attributes
     * @throws ElementValidationFailed
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
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
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
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
     * @param array $attributes
     * @return Element
     * @throws ElementValidationFailed
     * @throws ElementNotRegisteredException
     * @throws ElementNotInstalledException
     * @throws Exceptions\ElementsNotInstalledException
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

        return $entity->toElement($this->element->getAlias());
    }
}
