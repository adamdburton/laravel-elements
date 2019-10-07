<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Models\Property;
use Illuminate\Database\Eloquent\Builder as Eloquent;

/**
 * Trait QueriesRelatedElements
 * @method Property getPropertyModel($key)
 * @method PropertyDefinition getPropertyDefinition($key)
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

            $this->whereRelation($this->query(), $property, $operator, $value);
        } else {
            // e.g. localProperty = value

            $this->whereProperty($this->query(), $property, $operator, $value);
        }

        return $this;
    }
    /**
     * @param Eloquent $query
     * @param string $property
     * @param string $operator
     * @param $value
     */
    protected function whereProperty(Eloquent $query, string $property, $operator = '', $value = null)
    {
        /** @var Property $propertyModel */
        $propertyModel = $this->getPropertyModel($property);

        $query->whereHas('properties', function (Eloquent $query) use ($propertyModel, $operator, $value) {
            $query
                ->where('property_id', $propertyModel->id)
                ->where($propertyModel->pivotColumnKey() . '_value', $value ? $operator : '=', $value ?? $operator);
        });
    }

    /**
     * @param Eloquent $query
     * @param $property
     * @param $operator
     * @param $value
     */
    protected function whereRelationProperty(Eloquent $query, string $property, $operator, $value)
    {
        $propertyDefinition = $this->getPropertyDefinition($property);

        $query->whereHas(
            'relations',
            function (Eloquent $query) use ($propertyDefinition, $property, $operator, $value) {
                $query->where('type', $propertyDefinition->getMeta('elementType'));

                $this->whereProperty($query, $property, $operator, $value);
            }
        );
    }
}
