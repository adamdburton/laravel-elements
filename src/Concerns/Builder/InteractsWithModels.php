<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Element;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Models\Entity;
use Click\Elements\Models\Property;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trait QueriesRelatedElements
 * @method Property getPropertyModel($key)
 * @method PropertyDefinition getPropertyDefinition($key)
 * @method ElementDefinition getElementDefinition()
 */
trait InteractsWithModels
{
    /**
     * @param array $attributes
     * @return Entity
     */
    protected function createEntity($attributes = [])
    {
        /** @var Entity $entity */
        $entity = Entity::create(['type' => $this->element->getAlias()]);

        $a = $this->setEntityAttributes($entity, $attributes);

        return $a;
    }

    /**
     * @param Entity $entity
     * @param $attributes
     * @return Entity
     */
    protected function updateEntity(Entity $entity, $attributes)
    {
        $entity->touch();

        return $this->setEntityAttributes($entity, $attributes);
    }

    /**
     * @param Entity $entity
     * @param $attributes
     * @return Entity
     */
    protected function setEntityAttributes(Entity $entity, $attributes = [])
    {
        $properties = $this->getElementDefinition()->getPropertyDefinitions();
        $propertyModels = $this->getElementDefinition()->getPropertyModels();

        $relations = [];

        foreach ($properties as $property) {
            $key = $property->getKey();

            if (isset($propertyModels[$key]) && isset($attributes[$key])) {
                $propertyModel = $propertyModels[$key];

                $relations[$propertyModel->id] = [$propertyModel->pivotColumnKey() => $attributes[$key]];
            }
        }

        $entity->properties()->sync($relations);

        $entity->load('properties');

        return $entity;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->query()->exists();
    }

    /**
     * @return Element[]
     */
    public function get()
    {
        return $this->mapIntoElements($this->query()->get());
    }

    /**
     * @param $primaryKey
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    public function find($primaryKey)
    {
        return $this->mapIntoElement($this->query()->find($primaryKey));
    }

    /**
     * @param $primaryKeys
     * @return Element[]
     */
    public function findMany($primaryKeys)
    {
        return $this->mapIntoElements($this->query()->findMany($primaryKeys));
    }

    /**
     * @param Entity $model
     * @return Element
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    protected function mapIntoElement(Entity $model)
    {
        $class = $this->getElementDefinition()->getClass();

        return $model->toElement($class);
    }

    /**
     * @param Collection $models
     * @return Element[]
     */
    protected function mapIntoElements(Collection $models)
    {
        $relations = $this->getWiths($models);

        $class = $this->getElementDefinition()->getClass();

        return $models->map(function (Entity $model) use ($class, $relations) {
            return $model->toElement($class, $relations);
        })->all();
    }
}
