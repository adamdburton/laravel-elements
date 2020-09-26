<?php

namespace Click\Elements;

use Click\Elements\Concerns\Element\HasAttributes;
use Click\Elements\Concerns\Element\HasMeta;
use Click\Elements\Concerns\Element\HasScopes;
use Click\Elements\Concerns\Element\MocksElements;
use Click\Elements\Contracts\ElementContract;
use Click\Elements\Definitions\AttributeDefinition;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Exceptions\Attribute\AttributeValueTypeInvalidException;
use Click\Elements\Exceptions\AttributeSchema\AttributeSchemaClassInvalidException;
use Click\Elements\Models\Attribute;
use Click\Elements\Models\Entity;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Types\AttributeType;
use Click\Elements\Types\RelationType;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * The base Element class. You should extend this!
 *
 * @method static Element find($id)
 * @see Builder::find()
 *
 * @method static Builder where($attribute, $operator = '=', $value = null)
 * @see Builder::where()
 *
 * @method static Builder has($attribute, Closure $callback)
 * @see Builder::has()
 *
 * @method static Builder doesntHave($attribute, Closure $callback)
 * @see Builder::doesntHave()
 *
 * @method static Builder whereHas($attribute, Closure $callback)
 * @see Builder::whereHas()
 *
 * @method static Builder whereDoesNotHave($attribute, Closure $callback)
 * @see Builder::whereDoesNotHave()
 */
abstract class Element implements ElementContract
{
    use HasAttributes;
    use HasScopes;
    use HasMeta;
    use ForwardsCalls;
    use MocksElements;

    /**
     * @var string
     */
    protected $typeName;

    /**
     * @var string
     */
    protected $aliasName;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @param null $attributes
     * @throws AttributeValueTypeInvalidException
     * @throws Exceptions\Attribute\AttributeNotDefinedException
     * @throws Exceptions\Attribute\AttributeValidationFailedException
     * @throws Exceptions\Relation\ManyRelationInvalidException
     * @throws Exceptions\Relation\SingleRelationInvalidException
     */
    public function __construct($attributes = null)
    {
        if ($attributes) {
            $this->setAttributes($attributes);
        }
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws Exceptions\Attribute\AttributeValidationFailedException
     * @throws Exceptions\Relation\ManyRelationInvalidException
     * @throws Exceptions\Relation\SingleRelationInvalidException
     * @throws AttributeValueTypeInvalidException
     * @throws Exceptions\Attribute\AttributeNotDefinedException
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static())->$method(...$parameters);
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->query(), $method, $parameters);
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return new Builder($this);
    }

    /**
     * @return array
     */
    public function toJson()
    {
        return [
            'meta' => $this->getMeta(),
            'attributes' => $this->getAttributes(),
            'values' => $this->getAttributeValues()
        ];
    }

    /**
     * @return array
     */
    protected function getAttributes()
    {
        $definitions = collect($this->getElementDefinition()->getAttributeDefinitions());

        return $definitions->values()->map(function (AttributeDefinition $definition) {
            return $definition->toJson();
        })->keyBy('key')->all();
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->aliasName ?: Str::camel(class_basename($this));
    }

    /**
     * @return Entity
     */
    public function getEntity()
    {
        if (!isset($this->entity)) {
            $this->entity = Entity::findOrFail($this->getId());
        }

        return $this->entity;
    }

    /**
     * @param Entity $entity
     * @return Element
     */
    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;

        $attributes = $this->getEntityAttributeValues();

        $this->setRawAttributes($attributes);

        $this->setMeta($entity->getMeta());

        return $this;
    }

    /**
     * @return array
     */
    protected function getEntityAttributeValues()
    {
        $values = $this->entity->attributeValues;

        return $values->mapToDictionary(function (Attribute $attribute) {
            return [$attribute->key => $attribute->getEntityAttributeValue()];
        })->mapWithKeys(function ($values, $key) {
            $attributeDefinition = $this->getAttributeDefinition($key);

            if ($attributeDefinition->getType() === AttributeType::RELATION) {
                $relationType = $attributeDefinition->getMeta('relationType');

                if ($relationType === RelationType::SINGLE) {
                    $values = $values[0];
                }
            } else {
                $values = $values[0]; // Grab the first value by key
            }

            return [$key => $values];
        })->all();
    }

    /**
     * @param array $meta
     * @return Element
     */
    public function setMeta(array $meta)
    {
        if (isset($meta['id'])) {
            $this->id = $meta['id'];
        }

        if (isset($meta['type'])) {
            $this->type = $meta['type'];
        }

        return $this;
    }

    /**
     * @return ElementDefinition
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function getDefinition()
    {
        return new ElementDefinition($this->getClass());
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return get_class($this);
    }

    /**
     * @param ElementSchema $schema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function buildMetaDefinition(ElementSchema $schema)
    {
        $schema->timestamp('createdAt')->label('Created');
        $schema->timestamp('updatedAt')->label('Updated');
    }
}
