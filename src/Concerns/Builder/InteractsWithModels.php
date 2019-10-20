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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Trait QueriesRelatedElements
 * @method Property getPropertyModel($key)
 * @method PropertyDefinition getPropertyDefinition($key)
 * @method ElementDefinition getElementDefinition()
 */
trait InteractsWithModels
{
    /**
     * @param Model $entity
     * @return Element
     */
    protected function mapIntoElement(Model $entity)
    {
        $collection = collect([$entity]);

        $firstElement = $this->mapIntoElements($collection)->first();

        return $firstElement;
    }

    /**
     * @param BaseCollection $models
     * @return Collection
     */
    protected function mapIntoElements(BaseCollection $models)
    {
        $relations = $this->getWiths($models);

        return Collection::fromEntities($models)->setRelations($relations);
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
     * @param array $attributes
     * @return Entity
     * @throws ElementNotInstalledException
     */
    protected function createRawEntity($attributes = [])
    {
        /** @var Entity $entity */
        $entity = Entity::create(['type' => $this->element->getAlias()]);

        $propertyModels = $this->getElementDefinition()->getPropertyModels();

        $pivots = [];

        foreach ($attributes as $key => $value) {
            if (isset($propertyModels[$key])) {
                $model = $propertyModels[$key];

                $pivots[$model->id] = [$model->getPivotColumnKey() => $value];
            }
        }

        if (count($pivots)) {
            $entity->properties()->attach($pivots);
        }

        return $entity;
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
     * @param PropertyDefinition $definition
     * @param Property $model
     * @param $attribute
     */
    protected function setEntityAttribute(Entity $entity, PropertyDefinition $definition, Property $model, $attribute)
    {
        $relationType = $definition->getMeta('relationType');

        if ($definition->getType() === PropertyType::RELATION && $relationType === RelationType::MANY) {
            if (!$entity->wasRecentlyCreated) {
                $entity->properties()->detach($model->id);
            }

            foreach ($attribute as $item) {
                $meta = [$model->getPivotColumnKey() => $item];

                $entity->properties()->attach($model->id, $meta);
            }
        } else {
            $meta = [$model->getPivotColumnKey() => $attribute];

            if ($entity->wasRecentlyCreated) {
                $entity->properties()->attach($model->id, $meta);
            } else {
                $entity->properties()->updateExistingPivot($model->id, $meta);
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
