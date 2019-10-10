<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Builder;
use Click\Elements\Definitions\PropertyDefinition;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException as ElementNotRegisteredExceptionAlias;
use Click\Elements\Exceptions\ElementsNotInstalledException as ElementsNotInstalledExceptionAlias;
use Click\Elements\Models\Property;
use Closure;
use Illuminate\Database\Eloquent\Builder as Eloquent;

/**
 * Trait QueriesRelatedElements
 *
 * @method Property getPropertyModel($key)
 * @method PropertyDefinition getPropertyDefinition($key)
 * @method Builder query()
 */
trait QueriesRelatedElements
{
    /**
     * @param $relationProperty
     * @param Closure $callback
     * @return QueriesRelatedElements
     * @throws ElementNotRegisteredExceptionAlias
     * @throws ElementsNotInstalledExceptionAlias
     */
    public function whereHas($relationProperty, Closure $callback)
    {
        $propertyModel = $this->getPropertyModel($relationProperty);
        $elementQuery = $this->getRelationQuery($relationProperty);

        $this->query()->whereHas('relatedElements', function (Eloquent $query) use ($propertyModel, $elementQuery, $callback) {
            $query->where('property_id', $propertyModel->id);

            $elementQuery->setQuery($query);

            $callback($elementQuery);
        });

        return $this;
    }
}
