<?php

namespace Click\Elements;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Models\Entity;
use Click\Elements\Types\RelationType;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Class Collection
 */
class Collection extends BaseCollection
{
    protected $elementType;

    /**
     * Converts a collection of Entity models into a collection of Elements
     *
     * @param BaseCollection $entities
     * @return static
     */
    public static function fromEntities(BaseCollection $entities)
    {
        return new static($entities->map(function (Entity $model) {
            return $model->toElement();
        })->all());
    }

    /**
     * @param $relations
     * @return Collection
     */
    public function setRelations($relations)
    {
        $this->each(function (Element $element) use ($relations) {
            $attributes = $element->getRawAttributes();

            foreach ($relations as $key => $elements) {
                if ($element->hasRelation($key)) {
                    $type = $element->getRelationType($key);

                    if ($type === RelationType::SINGLE) {
                        $relation = $relations[$key]->get($attributes[$key]);

                        if ($relation) {
                            $element->setRelation($key, $relation);
                        }
                    } elseif ($type === RelationType::MANY) {
                        $relation = $relations[$key]->only($attributes[$key])->all();

                        if ($relation) {
                            $element->setRelation($key, $relation);
                        }
                    }
                }
            }
        });

        return $this;
    }

    /**
     * @return ElementDefinition
     * @throws Exceptions\Element\ElementNotRegisteredException
     */
    public function getElementType()
    {
        return $this->first()->getElementDefinition();
    }


    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @param callable|null $callback
     * @param mixed $default
     * @return Element
     */
    public function first(callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }
}
