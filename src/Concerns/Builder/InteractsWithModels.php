<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Collection;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Element;
use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Models\Entity;
use Click\Elements\Models\Property;
use Click\Elements\Types\PropertyType;
use Click\Elements\Types\RelationType;
use Illuminate\Database\Eloquent\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait QueriesRelatedElements
 * @method Property getPropertyModel($key)
 * @method PropertyDefinition getPropertyDefinition($key)
 * @method ElementDefinition getElementDefinition()
 */
trait InteractsWithModels
{
    /**
     * @param BaseCollection $models
     * @return Collection
     */
    protected function mapIntoElements(BaseCollection $models)
    {
        $relations = $this->getWiths($models);

        return new Collection($models, $relations);
    }

    /**
     * @param array $attributes
     * @return Entity
     * @throws ElementNotInstalledException
     */
    protected function createEntity($attributes = [])
    {
        /** @var Entity $entity */
        $entity = Entity::create(['type' => $this->element->getAlias()]);

        return $this->setEntityAttributes($entity, $attributes);
    }

    /**
     * @param Entity $entity
     * @param $attributes
     * @return Entity
     * @throws ElementNotInstalledException
     */
    protected function setEntityAttributes(Entity $entity, $attributes = [])
    {
        $properties = $this->getElementDefinition()->getPropertyDefinitions();
        $propertyModels = $this->getElementDefinition()->getPropertyModels();

        foreach ($properties as $property) {
            $key = $property->getKey();

            if (isset($propertyModels[$key]) && isset($attributes[$key])) {
                $propertyModel = $propertyModels[$key];

                $this->setEntityAttribute($entity, $property, $propertyModel, $attributes[$key]);
            }
        }

        return $entity;
    }

    /**
     * @param Entity $entity
     * @param PropertyDefinition $property
     * @param Property $model
     * @param $attribute
     */
    protected function setEntityAttribute(Entity $entity, PropertyDefinition $property, Property $model, $attribute)
    {
        $relationType = $property->getMeta('relationType');

        if ($property->getType() === PropertyType::RELATION && $relationType === RelationType::MANY) {
            if (!$entity->wasRecentlyCreated) {
                $entity->properties()->detach($model->id);
            }

            foreach ($attribute as $item) {
                $meta = [$model->pivotColumnKey() => $item];

                $entity->properties()->attach($model->id, $meta);
            }
        } else {
            $meta = [$model->pivotColumnKey() => $attribute];

            if (!$entity->wasRecentlyCreated) {
                $entity->properties()->updateExistingPivot($model->id, $meta);
            } else {
                $entity->properties()->attach($model->id, $meta);
            }
        }
    }

    /**
     * @param Entity $entity
     * @param $attributes
     * @return Entity
     * @throws ElementNotInstalledException
     */
    protected function updateEntity(Entity $entity, $attributes)
    {
        $entity->touch();

        return $this->setEntityAttributes($entity, $attributes);
    }
}