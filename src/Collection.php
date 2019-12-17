<?php

namespace Click\Elements;

use Click\Elements\Types\RelationType;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Class Collection
 */
class Collection extends BaseCollection
{
    /**
     * @param $relations
     * @return Collection
     */
    public function setRelations(BaseCollection $relations)
    {
        return $this->each(function (Element $element) use ($relations) {
            $attributes = $element->getRawAttributes();

            foreach ($relations as $key => $elements) {
                $keys = $attributes[$key];

                $elementDefinition = $element->getElementDefinition()->getAttributeDefinition($key);
                $relationType = $elementDefinition->getMeta('relationType');

                if ($relationType == RelationType::MANY) {
                    $relation = $relations->get($key)->only($keys);
                } else {
                    $relation = $relations->get($key)->get($keys);
                }

                if ($relation) {
                    $element->setRelation($key, $relation);
                }
            }
        });
    }

    /**
     * @return Collection
     */
    public function getElementDefinitions()
    {
        return $this->map(function (Element $element) {
            return $element->getElementDefinition();
        })->unique();
    }
}
