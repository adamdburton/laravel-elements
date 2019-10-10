<?php

namespace Click\Elements;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Models\Entity;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Class Collection
 */
class Collection extends BaseCollection
{
    protected $elementType;

    public function __construct(BaseCollection $items, array $relations = null)
    {
        parent::__construct($this->toElements($items, $relations));
    }

    /**
     * Converts a collection of Entity models into a collection of Elements
     *
     * @param BaseCollection $items
     * @param array|null $relations
     * @return Element[]
     */
    protected function toElements(BaseCollection $items, array $relations = null)
    {
        return $items->map(function (Entity $model) use ($relations) {
            return $model->toElement();
        })->all();
    }

    /**
     * @return ElementDefinition
     * @throws Exceptions\Element\ElementNotRegisteredException
     * @throws Exceptions\ElementsNotInstalledException
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
