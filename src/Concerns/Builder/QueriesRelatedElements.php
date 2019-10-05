<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotInstalledException;
use Click\Elements\Models\Property;
use Illuminate\Database\Eloquent\Builder as Eloquent;

/**
 * Trait QueriesRelatedElements
 * @method Property getPropertyModel($key)
 * @method PropertyDefinition getPropertyDefinition($key)
 */
trait QueriesRelatedElements
{

    /**
     * @param Eloquent $query
     * @param string $property
     * @param string $operator
     * @param $value
     * @throws ElementNotInstalledException
     * @throws PropertyNotInstalledException
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
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     * @throws PropertyNotInstalledException
     */
    protected function whereRelation(Eloquent $query, $property, $operator, $value)
    {
        // e.g. localProperty.foreignProperty = 123
        // e.g. localProperty.foreignRelation.foreignProperty = 123


        $parts = explode('.', $property);

        $localPropertyName = $parts[0];
        $foreignPropertyName = $parts[1];

        if (strstr($foreignPropertyName, '.')) {
            // Foreign relation property where
        } else {
            // Foreign property where
            $this->whereRelationProperty($foreignPropertyName, $operator, $value);
        }

        $propertyModel = $this->getPropertyModel();
        $propertyDefinition = $this->getPropertyDefinition($property);

        $query->whereHas('relations', function (Eloquent $query) {
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
