<?php

namespace Click\Elements;

use Click\Elements\Concerns\Builder\InteractsWithEntities;
use Click\Elements\Concerns\Builder\QueriesAttributes;
use Click\Elements\Concerns\Builder\RaisesEvents;
use Click\Elements\Definitions\AttributeDefinition;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Exceptions\Attribute\AttributeNotDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeValidationFailedException;
use Click\Elements\Exceptions\Attribute\AttributeValueTypeInvalidException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Exceptions\Relation\SingleRelationInvalidException;
use Click\Elements\Models\Attribute;
use Click\Elements\Models\Entity;
use Closure;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * Element query builder
 */
class Builder
{
    use ForwardsCalls;
    use InteractsWithEntities;
    use QueriesAttributes;
    use RaisesEvents;

    /**
     * @var Element
     */
    protected $element;

    /**
     * @var Eloquent
     */
    protected $query;

    /**
     * @var array
     */
    protected $withs = [];

    /**
     * @param Element $element
     */
    public function __construct(Element $element)
    {
        $this->element = $element;
    }

    /**
     * @param $name
     * @param $arguments
     * @return Builder|mixed
     * @throws ElementNotRegisteredException
     */
    public function __call($name, $arguments)
    {
        if ($this->element->hasScope($name) || $this->element->hasRelation($name)) {
            return $this->applyCallback($name, $arguments);
        }

        $this->forwardCallTo($this->getBase(), $name, $arguments);

        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this|Builder
     * @throws ElementNotRegisteredException
     */
    protected function applyCallback($name, $arguments)
    {
        if ($this->element->hasScope($name)) {
            $this->element->applyScope($name, $this, $arguments);
        }

        if ($this->element->hasRelation($name)) {
            return $this->getRelationBuilder($name);
        }

        return $this;
    }

    /**
     * @return Eloquent
     */
    protected function getBase()
    {
        if (!$this->query) {
            $this->refresh();
        }

        return $this->query;
    }

    /**
     * @return Builder
     */
    public function refresh()
    {
        $this->query = Entity::query()
            ->with('attributeValues')
            ->where('type', $this->element->getAlias());

        return $this;
    }

    /**
     * @param string $attribute
     * @return AttributeDefinition|null
     * @throws ElementNotRegisteredException
     * @throws AttributeNotDefinedException
     */
    public function getAttributeDefinition(string $attribute)
    {
        return $this->getElementDefinition()->getAttributeDefinition($attribute);
    }

    /**
     * @return ElementDefinition
     * @throws ElementNotRegisteredException
     */
    public function getElementDefinition()
    {
        return elements()->getElementDefinition($this->element->getAlias());
    }

    /**
     * @return Collection
     * @throws ElementNotRegisteredException
     */
    public function all()
    {
        return $this->refresh()->get();
    }

    /**
     * @return Collection
     * @throws ElementNotRegisteredException
     */
    public function get()
    {
        $elements = $this->fetchElements();
        $relations = $this->fetchWiths($elements);

        return $elements->setRelations($relations);
    }

    /**
     * @return Collection
     */
    protected function fetchElements()
    {
        $entities = $this->getBase()->get();

        return Collection::make($entities->map(function (Entity $entity) {
            return $this->getElementDefinition()->factory()->setEntity($entity);
        })->all());
    }

    /**
     * @param Collection $elements
     * @return Collection
     * @throws ElementNotRegisteredException
     */
    protected function fetchWiths(Collection $elements)
    {
        $definitions = $this->getElementDefinition()->getAttributeDefinitions();

        return collect($this->withs)->mapWithKeys(function ($a, $b) use ($elements, $definitions) {
            $key = $a instanceof Closure ? $b : $a;
            $callback = $a instanceof Closure ? $a : null;

            $attributeDefinition = $definitions[$key];

            $primaryKeys = $elements->map(function (Element $element) use ($key) {
                $attributes = $element->getRawAttributes();

                // TODO: Exception for primary key not found? (database modified)
                // TODO: Add test for loading with for multiple elements, with and without that relation set

                return $attributes[$key];
            })->flatten()->unique()->all();

            $builder = element($attributeDefinition->getMeta('elementType'));

            if ($callback) {
                $callback($builder);
            }

            $values = $builder->findMany($primaryKeys)->keyBy(function (Element $element) {
                return $element->getId();
            });

            return [$key => $values];
        });
    }

    /**
     * @param array $ids
     * @return Collection
     * @throws ElementNotRegisteredException
     */
    public function findMany(array $ids)
    {
        $this->getBase()->whereIn('id', $ids);

        return $this->get();
    }

    /**
     * @param $id
     * @return Element
     * @throws ElementNotRegisteredException
     */
    public function find($id)
    {
        $this->getBase()->where('id', $id);

        return $this->first();
    }

    /**
     * @return Element|null
     * @throws ElementNotRegisteredException
     */
    public function first()
    {
        $this->getBase()->limit(1);

        return $this->get()->first();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->getBase()->exists();
    }

    /**
     * @return bool
     */
    public function toSql()
    {
        return Str::replaceArray('?', $this->getBase()->getBindings(), $this->getBase()->toSql());
    }

    /**
     * @param $withs
     * @return Builder
     */
    public function with($withs)
    {
        $this->withs = Arr::wrap($withs);

        return $this;
    }

    /**
     * @param Eloquent $builder
     * @return Builder
     */
    public function setBase(Eloquent $builder)
    {
        $this->query = $builder;

        return $this;
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws AttributeValidationFailedException
     * @throws AttributeValueTypeInvalidException
     * @throws ManyRelationInvalidException
     * @throws SingleRelationInvalidException
     * @throws AttributeNotDefinedException
     */
    public function create(array $attributes = [])
    {
        $this->element->setAttributes($attributes); // Triggers validation

        $this->fireEvent('creating');
        $this->fireEvent('saving');

        $attributes = $this->element->getRawAttributes(); // Gets validated, normalised attributes

        $entity = $this->createEntity($attributes);

        $this->element->setEntity($entity);

        $this->fireEvent('created');
        $this->fireEvent('saved');

        return $this->element;
    }

    /**
     * @param array $attributes
     * @return Element
     * @throws AttributeNotDefinedException
     * @throws AttributeValidationFailedException
     * @throws AttributeValueTypeInvalidException
     * @throws ManyRelationInvalidException
     * @throws SingleRelationInvalidException
     */
    public function update($attributes = [])
    {
        $this->element->setAttributes($attributes); // Triggers validation

        $this->fireEvent('updating');
        $this->fireEvent('saving');

        $attributes = $this->element->getRawAttributes(); // Gets validated, normalised attributes

        $this->updateEntity($attributes);

        $this->fireEvent('updated');
        $this->fireEvent('saved');

        return $this->element;
    }

    /**
     * Creates an element without type checking and validation
     *
     * @param array $attributes
     * @return Element
     */
//    public function createRaw(array $attributes)
//    {
//        $attributes = $this->mergeMetaAttributes($attributes);
//
//        $this->element->setRawAttributes($attributes);
//
//        $this->fireEvent('creating');
//        $this->fireEvent('saving');
//
//        $this->element->setEntity($this->createEntity($attributes));
//
//        $this->fireEvent('created');
//        $this->fireEvent('saved');
//
//        return $this->element;
//    }

    /**
     * @param string $attribute
     * @return Attribute
     * @throws ElementNotRegisteredException
     * @throws AttributeNotDefinedException
     */
    protected function getAttributeModel(string $attribute)
    {
        return $this->getElementDefinition()->getAttributeModel($attribute);
    }

    /**
     * @param array $attributes
     * @return Element
     */
//    public function updateRaw(array $attributes)
//    {
//        $this->fireEvent('updating');
//        $this->fireEvent('saving');
//
//        $entity = $this->updateEntity($attributes);
//
//        $this->element->setEntity($entity);
//
//        $this->fireEvent('updated');
//        $this->fireEvent('saved');
//
//        return $this->element;
//    }
}
