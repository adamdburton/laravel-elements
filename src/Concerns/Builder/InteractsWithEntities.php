<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Definitions\AttributeDefinition;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Element;
use Click\Elements\Models\Attribute;
use Click\Elements\Models\Entity;
use Click\Elements\Pivots\Value;
use Click\Elements\Types\AttributeType;
use Click\Elements\Types\RelationType;
use Illuminate\Database\Eloquent\Builder as Eloquent;

/**
 * Trait QueriesRelatedElements
 * @property Element $element
 * @method Attribute getAttributeModel($key)
 * @method AttributeDefinition getAttributeDefinition($key)
 * @method ElementDefinition getElementDefinition()
 * @method string getLocale()
 * @method mixed getAttribute(string $attribute)
 */
trait InteractsWithEntities
{
    /**
     * @param array $attributes
     * @return Entity
     */
    protected function createRawEntity(array $attributes = [])
    {
        $entity = $this->createEntity();

        return $this->setRawEntityAttributes($entity, $attributes);
    }

    /**
     * @param array $attributes
     * @return Entity
     */
    protected function createEntity(array $attributes = [])
    {
        /** @var Entity $entity */
        $entity = Entity::create([
            'type' => $this->element->getAlias()
        ]);

        $this->setEntityAttributes($entity, $attributes);

        return $entity;
    }

    /**
     * @param Entity $entity
     * @param array $attributeValues
     * @return Entity
     */
    protected function setEntityAttributes(Entity $entity, array $attributeValues = [])
    {
        foreach ($attributeValues as $attribute => $value) {
            $this->setEntityAttribute($entity, $attribute, $value);
        }

        return $entity;
    }

    /**
     * @param Entity $entity
     * @param string $attribute
     * @param $value
     */
    protected function setEntityAttribute(Entity $entity, string $attribute, $value)
    {
        $definition = $this->getAttributeDefinition($attribute);
        $model = $this->getAttributeModel($attribute);

        $relationType = $definition->getMeta('relationType');

        if ($definition->getType() === AttributeType::RELATION && $relationType === RelationType::MANY) {
            if (!$entity->wasRecentlyCreated) {
                $entity->attributeValues()->detach($model->id);
            }

            foreach ($value as $item) {
                $meta = [$model->getEntityAttributeKey() => $item];

                $entity->attributeValues()->attach($model->id, $meta);
            }
        } else {
            $meta = [$model->getEntityAttributeKey() => $value];

            if ($entity->wasRecentlyCreated) {
                $entity->attributeValues()->attach($model->id, $meta);
            } else {
                $entity->attributeValues()->updateExistingPivot($model->id, $meta);
            }
        }
    }

    /**
     * @param array $attributes
     * @return Entity
     */
    protected function updateEntity(array $attributes = [])
    {
        $entity = $this->createEntityVersion();

        $this->setEntityAttributes($entity, $attributes);

        return $entity;
    }

    /**
     * @return Entity
     */
    protected function createEntityVersion()
    {
        return Entity::create([
            'type' => $this->element->getAlias()
        ]);
    }
}
