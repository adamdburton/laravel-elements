<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Models\Property;
use Closure;
use Illuminate\Database\Eloquent\Builder as Eloquent;

/**
 * Trait QueriesRelatedElements
 *
 * @method Property getPropertyModel($key)
 * @method PropertyDefinition getPropertyDefinition($key)
 * @method Eloquent query()
 */
trait QueriesRelatedElements
{
    /**
     * @param $relationProperty
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @param Closure|null $callback
     * @return $this
     */
    public function has(string $relationProperty, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
    {
        $this->query()->has(
            'relatedElements',
            $operator,
            $count,
            $boolean,
            function (Eloquent $query) use ($relationProperty, $callback) {
                $propertyModel = $this->getPropertyModel($relationProperty);

                $query->where('property_id', $propertyModel->id);

                if ($callback) {
                    $elementQuery = $this->getRelationQuery($relationProperty)->setQuery($query);

                    $callback($elementQuery);
                }
            }
        );

        return $this;
    }

    /**
     * @param string $relationProperty
     * @param Closure|null $callback
     * @param string $boolean
     * @return QueriesRelatedElements
     */
    public function doesntHave(string $relationProperty, Closure $callback = null, $boolean = 'and')
    {
        return $this->has($relationProperty, '<', 1, $boolean, $callback);
    }

    /**
     * @param $relationProperty
     * @param Closure $callback
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @return QueriesRelatedElements
     */
    public function whereHas(string $relationProperty, Closure $callback, $operator = '>=', $count = 1, $boolean = 'and')
    {
        return $this->has($relationProperty, $operator, $count, $boolean, $callback);
    }

    /**
     * @param string $relationProperty
     * @param Closure $callback
     * @param string $boolean
     * @return QueriesRelatedElements
     */
    public function whereDoesntHave(string $relationProperty, Closure $callback, $boolean = 'and')
    {
        return $this->whereHas($relationProperty, $callback, '<', 1, $boolean);
    }
}
