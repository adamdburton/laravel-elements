<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Element;
use Click\Elements\Models\Property;
use Illuminate\Database\Eloquent\Builder as Eloquent;

/**
 * Trait QueriesRelatedElements
 * @method Property getPropertyModel($key)
 * @method PropertyDefinition getPropertyDefinition($key)
 * @method Eloquent query()
 */
trait QueriesProperties
{
    /**
     * @param string $property
     * @param string $operator
     * @param null $value
     * @return $this
     * @see Entity::scopeWhereHasProperty()
     */
    public function where(string $property, $operator = '', $value = null)
    {
        // We could be querying the elements property values OR the elements RELATIONS property values

        if (substr_count($property, '.') === 1) {
            // e.g. localRelationProperty.foreignProperty = value

            $this->whereRelationProperty($this->query(), $property, $value ? $operator : '=', $value ?: $operator);
        } else {
            // e.g. localProperty = value

            $this->whereProperty($this->query(), $property, $value ? $operator : '=', $value ?: $operator);
        }

        return $this;
    }

    /**
     * @param Eloquent $query
     * @param $property
     * @param $operator
     * @param $value
     */
    protected function whereRelationProperty(Eloquent $query, string $property, $operator, $value)
    {
        $prefix = explode('.', $property)[0];
        $property = substr($property, strlen($prefix));
    }

    /**
     * @param Eloquent $query
     * @param string $property
     * @param string $operator
     * @param $value
     */
    protected function whereProperty(Eloquent $query, string $property, $operator = '', $value = null)
    {
        $propertyModel = $this->getPropertyModel($property);

        $query->whereHas('properties', function (Eloquent $query) use ($propertyModel, $operator, $value) {
            $query->where('property_id', $propertyModel->id);

            if (is_array($value)) {
                $this->whereArrayProperty($query, $operator, $value);
            } else {
                $query->where($propertyModel->pivotColumnKey(), $value ? $operator : '=', $value ?: $operator);
            }
        });
    }

    /**
     * @param Eloquent $query
     * @param string $property
     * @param string $operator
     * @param null $value
     */
    protected function whereArrayProperty(Eloquent $query, $operator = '', $value = null)
    {
        $query->whereRaw(sprintf(
            'json_value %s cast(? as json)',
            $value ? $operator : '='
        ), json_encode($value ?: $operator));
    }
}
