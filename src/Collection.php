<?php

namespace Click\Elements;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Models\Entity;
use Illuminate\Database\Eloquent\Collection as Eloquent;

/**
 * Class Collection
 */
class Collection extends Eloquent
{
    protected $elementType;

    public function __construct(Eloquent $items, array $relations = null)
    {
        parent::__construct($this->toElements($items, $relations));
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
     * Converts a collection of Entity models into a collection of Elements
     *
     * @param Eloquent $items
     * @param array|null $relations
     * @return Element[]
     */
    protected function toElements(Eloquent $items, array $relations = null)
    {
        return $items->map(function (Entity $model) use ($relations) {
            if ($relations) {
                dd($relations);
            }

            return $model->toElement();
        })->all();
    }

    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return Element
     */
    public function first(callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }
}
