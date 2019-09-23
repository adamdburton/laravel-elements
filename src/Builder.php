<?php

namespace Click\Elements;

use Click\Elements\Models\Entity;

/**
 * Class Builder
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

        $this->query = Entity::query();
    }

    /**
     * @param $type
     * @return $this
     * @see Entity::scopeWhereHasProperty()
     */
    public function type($type)
    {
        $this->query->whereHasProperty('elementType.type', $type);

        return $this;
    }

    /**
     * @param $property
     * @param string $operator
     * @param null $value
     * @return $this
     * @see Entity::scopeWhereHasProperty()
     */
    public function where($property, $operator = '', $value = null)
    {
        $property = $this->element->getElementType()->getProperty($property);

        $this->query->whereHasProperty($property, $operator, $value);

        return $this;
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
     * @return mixed
     * @throws \Exception
     */
    public function create(array $attributes)
    {
        $this->validate($attributes);

        $relations = $this->buildRelations(
            $this->element->getProperties(),
            $attributes
        );

        /** @var Entity $entity */
        $entity = Entity::create();

        $entity->properties()->sync($relations);

        $element = $this->element->getElementDefinition()->factory($attributes, $entity->id);

        return $element;
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws Exceptions\ElementTypeMissingException
     * @throws Exceptions\PropertyMissingException
     */
    public function update(array $attributes)
    {
        $this->validate($attributes);

        $attributes = array_merge($this->element->getAttributes(), $attributes);

        $relations = $this->buildRelations(
            $this->element->getProperties(),
            $attributes
        );

        /** @var Entity $entity */
        $entity = Entity::find($this->element->getPrimaryKey());

        $entity->properties()->sync($relations);

        $element = $this->element->getElementDefinition()->factory($attributes);

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

    protected function validate(array $attributes)
    {
    }
}
