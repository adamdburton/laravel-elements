<?php

namespace Click\Elements\Concerns\Builder;

use Click\Elements\Builder;
use Click\Elements\Definitions\AttributeDefinition;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Models\Attribute;
use Closure;
use Illuminate\Database\Eloquent\Builder as Eloquent;

/**
 * Trait QueriesRelatedElements
 * @method Attribute getAttributeModel($key)
 * @method AttributeDefinition getAttributeDefinition($key)
 * @method Eloquent getBase()
 */
trait QueriesAttributes
{
    /**
     * @param string $relation
     * @return Builder
     * @throws ElementNotRegisteredException
     */
    public function getRelationBuilder(string $relation)
    {
        $attributeDefinition = $this->getAttributeDefinition($relation);

        $elementType = $attributeDefinition->getMeta('elementType');
        $elementDefinition = elements()->getElementDefinition($elementType);

        $element = $elementDefinition->factory();

        return $element->query();
    }

    /**
     * @param string $attribute
     * @param string $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where(string $attribute, $operator = '', $value = null, $boolean = 'and')
    {
        if (strpos($attribute, '.') !== false) {
            return $this->hasNested($attribute, $value ? $operator : '=', $value ?: $operator, $boolean);
        } else {
            return $this->whereAttribute($attribute, $value ? $operator : '=', $value ?: $operator, $boolean);
        }
    }

    /**
     * @param string $attribute
     * @param string $operator
     * @param null $value
     * @return $this
     */
    public function orWhere(string $attribute, $operator = '', $value = null)
    {
        return $this->where($attribute, $operator, $value, 'or');
    }

    /**
     * @param $relations
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @param null $callback
     * @return QueriesAttributes
     */
    protected function hasNested($relations, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
    {
        $relations = explode('.', $relations);

        $doesntHave = $operator === '<' && $count === 1;

        if ($doesntHave) {
            $operator = '>=';
            $count = 1;
        }

        $closure = function (Builder $query) use (&$closure, &$relations, $operator, $count, $callback) {
            count($relations) > 1
                ? $query->whereHas(array_shift($relations), $closure)
                : $query->has(array_shift($relations), $operator, $count, 'and', $callback);
        };

        return $this->has(array_shift($relations), $doesntHave ? '<' : '>=', 1, $boolean, $closure);
    }

    /**
     * @param Closure $callback
     * @param string $boolean
     * @return $this
     */
    protected function applyConstraint(Closure $callback, $boolean = 'and')
    {
        if ($boolean === 'and') {
            $this->getBase()->whereHas('attributeValues', $callback);
        } elseif ($boolean === 'or') {
            $this->getBase()->orWhereHas('attributeValues', $callback);
        }

        return $this;
    }

    /**
     * @param string $attribute
     * @param string $operator
     * @param $value
     * @param string $boolean
     * @return $this
     */
    protected function whereAttribute(string $attribute, $operator = '', $value = null, $boolean = 'and')
    {
        return $this->applyConstraint(function (Eloquent $query) use ($attribute, $operator, $value) {
            $attributeModel = $this->getAttributeModel($attribute);

            $query->where('attribute_id', $attributeModel->id);

            if (is_array($value)) {
                $query->whereRaw(sprintf(
                    'json_value %s cast(? as json)',
                    $value ? $operator : '='
                ), json_encode($value ?: $operator));
            } else {
                $query->where(
                    $attributeModel->getEntityAttributeKey(),
                    $value ? $operator : '=',
                    $value ?: $operator
                );
            }
        }, $boolean);
    }

    /**
     * @param string $attribute
     * @param $values
     * @return $this
     */
    public function orWhereIn(string $attribute, $values)
    {
        return $this->whereIn($attribute, $values, 'or');
    }

    /**
     * @param string $attribute
     * @param $values
     * @param string $boolean
     * @return $this
     */
    public function whereIn(string $attribute, $values, $boolean = 'and')
    {
        return $this->whereAttributeIn($attribute, $values, $boolean);
    }

    /**
     * @param string $attribute
     * @param array $values
     * @param string $boolean
     * @return $this
     */
    protected function whereAttributeIn(string $attribute, array $values, $boolean = 'and')
    {
        return $this->applyAttributeConstraint($attribute, function (Eloquent $query) use ($attribute, $values) {
            $attributeModel = $this->getAttributeModel($attribute);

            $query->whereIn($attributeModel->getEntityAttributeKey(), $values);
        }, $boolean);
    }

    /**
     * @param string $attribute
     * @param Closure $callback
     * @param string $boolean
     * @return $this
     */
    protected function applyAttributeConstraint(string $attribute, Closure $callback, $boolean = 'and')
    {
        return $this->applyConstraint(function (Eloquent $query) use ($attribute, $callback) {
            $attributeModel = $this->getAttributeModel($attribute);

            $query->where('attribute_id', $attributeModel->id);

            $callback($query);
        }, $boolean);
    }

    /**
     * @param string $relationAttribute
     * @param Closure|null $callback
     * @param string $boolean
     * @return QueriesAttributes
     */
    public function doesntHave(string $relationAttribute, Closure $callback = null, $boolean = 'and')
    {
        return $this->has($relationAttribute, '<', 1, $boolean, $callback);
    }

    /**
     * @param $attribute
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @param Closure|null $callback
     * @return QueriesAttributes
     */

    public function has($attribute, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
    {
        $this->getBase()->has(
            'relatedEntities',
            $operator,
            $count,
            $boolean,
            function (Eloquent $query) use ($attribute, $callback) {
                $attributeModel = $this->getAttributeModel($attribute);
                $query->where('attribute_id', $attributeModel->id);

                if ($callback) {
                    $builder = $this->getRelationBuilder($attribute)->setBase($query);

                    $callback($builder);
                }
            }
        );

        return $this;
    }

    /**
     * @param string $relationAttribute
     * @param Closure $callback
     * @param string $boolean
     * @return QueriesAttributes
     */
    public function whereDoesNotHave(string $relationAttribute, Closure $callback, $boolean = 'and')
    {
        return $this->whereHas($relationAttribute, $callback, '<', 1, $boolean);
    }

    /**
     * @param $relationAttribute
     * @param Closure $callback
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @return QueriesAttributes
     */
    public function whereHas(string $relationAttribute, Closure $callback, $operator = '>=', $count = 1, $boolean = 'and')
    {
        return $this->has($relationAttribute, $operator, $count, $boolean, $callback);
    }
}
