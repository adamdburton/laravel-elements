<?php

namespace Click\Elements;

use Click\Elements\Concerns\Builder\QueriesRelatedElements;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Element\ElementValidationFailed;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotRegisteredException;
use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Click\Elements\Models\Entity;
use Click\Elements\Types\PropertyType;
use Closure;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

/**
 * Element query builder
 */
class Builder
{
    use QueriesRelatedElements;

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

    public function __call($name, $arguments)
    {
        if ($this->element->hasScope($name)) {
            return $this->element->applyScope($name, $this->query(), $arguments);
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
        return elements()->getElementDefinition($this->element->getElementClass());
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
     * @param string $operator
     * @param null $value
     * @return $this
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws PropertyNotInstalledException
     * @see Entity::scopeWhereHasProperty()
     */
    public function where(string $property, $operator = '', $value = null)
    {
        // We could be querying the elements property values OR the elements RELATIONS property values

        if (substr_count($property, '.') >= 2) {
            // e.g. localRelationProperty.foreignRelationProperty.foreignProperty = value

            $this->whereRelationProperty($this->query(), $property, $operator, $value);
        } elseif (substr_count($property, '.') === 1) {
            // e.g. localRelationProperty.foreignProperty = value

            $this->whereRelation($this->query(), $property, $operator, $value);
        } else {
            // e.g. localProperty = value

            $this->whereProperty($this->query(), $property, $operator, $value);
        }

        return $this;
    }

    /**
     * @param string $property
     * @return Models\Property
     * @throws ElementNotInstalledException
     * @throws PropertyNotInstalledException
     */
    protected function getPropertyModel(string $property)
    {
        return $this->element->getElementDefinition()->getPropertyModel($property);
    }

    /**
     * @param string $property
     * @return PropertyDefinition|null
     */
    protected function getPropertyDefinition(string $property)
    {
        return $this->element->getElementDefinition()->getPropertyDefinition($property);
    }

    /**
     * @param Request $request
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
        return $this->mapIntoElements($this->query()->get());
    }

    /**
     * @param $primaryKey
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
     */
    public function find($primaryKey)
    {
        return $this->mapIntoElement($this->query()->find($primaryKey));
    }

    /**
     * @param $primaryKeys
     * @return Element[]
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
     */
    public function findMany($primaryKeys)
    {
        return $this->mapIntoElements($this->query()->findMany($primaryKeys));
    }

    /**
     * @param Entity $model
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
     */
    protected function mapIntoElement(Entity $model)
    {
        $class = $this->element->getElementDefinition()->getClass();

        return $model->toElement($class);
    }

    /**
     * @param Collection $models
     * @return Element[]
     */
    protected function mapIntoElements(Collection $models)
    {
        $relations = $this->getWiths($models);

        $class = $this->element->getElementDefinition()->getClass();

        return $models->map(function (Entity $model) use ($class, $relations) {
            return $model->toElement($class, $relations);
        })->all();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->query()->exists();
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws PropertyNotRegisteredException
     * @throws PropertyValueInvalidException
     * @throws Exceptions\Property\PropertyNotRegisteredException
     */
    public function create(array $attributes)
    {
        $elementDefinition = $this->getElementDefinition();

        $element = $elementDefinition->factory($attributes); // Validates for free

        $propertyModels = $elementDefinition->getPropertyModels();
        $properties = $elementDefinition->getProperties();

        $entity = $this->newEntity();

        $attributes = array_merge($attributes, ['type' => $element->getAlias()]);

        foreach ($attributes as $attribute => $value) {
            $property = $properties[$attribute];

            if ($property->getType() === PropertyType::RELATION) {
                $this->element->setAttribute($attribute, $value);
            } else {
                $propertyModel = $propertyModels[$attribute];

                $entity->properties()->attach($propertyModel->id, [
                    $propertyModel->pivotColumnKey() => $value
                ]);
            }
        }

        return $entity->toElement($this->element->getAlias());
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     * @throws ElementValidationFailed
     * @throws Exceptions\ElementsNotInstalledException
     * @throws Exceptions\Property\PropertyNotRegisteredException
     * @throws Exceptions\Property\PropertyValueInvalidException
     */
    public function update(array $attributes)
    {
        $attributes = array_merge($this->element->getAttributes(), $attributes);

        $element = $this->getElementDefinition()->factory($attributes);

        $this->validate($element->getAttributes());


        return $entity->toElement($this->element->getAlias());
    }

    /**
     * @param Collection $models
     * @return Element[]
     */
    protected function getWiths(Collection $models)
    {
        $properties = $this->getElementDefinition()->getProperties();

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

    /**
     * @return Entity
     */
    protected function newEntity()
    {
        return Entity::create();
    }
}
