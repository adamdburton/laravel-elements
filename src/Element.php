<?php

namespace Click\Elements;

use Carbon\Carbon;
use Click\Elements\Concerns\Element\HasScopes;
use Click\Elements\Concerns\Element\HasTypedProperties;
use Click\Elements\Contracts\ElementContract;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotRegisteredException;
use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Click\Elements\Models\Entity;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * The base Element class. You should extend this!
 *
 * @method static Element create(array $attributes)
 * @see Builder::create()
 *
 * @method static Element createRaw(array $attributes)
 * @see Builder::createRaw()
 *
 * @method static Element update(array $attributes)
 * @see Builder::update()
 *
 * @method static Element updateRaw(array $attributes)
 * @see Builder::updateRaw()
 *
 * @method static Builder where($property, $operator = '=', $value = null)
 * @see Builder::where()
 *
 * @method static Builder whereHas($property, Closure $callback)
 * @see Builder::whereHas()
 *
 */
abstract class Element implements ElementContract
{
    use HasTypedProperties;
    use HasScopes;
    use ForwardsCalls;

    /**
     * @var int
     */
    protected $primaryKey;

    /**
     * @var Carbon
     */
    protected $createdAt;

    /**
     * @var Carbon
     */
    protected $updatedAt;

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
     * @param null $attributes
     * @param bool $raw
     * @throws Exceptions\Property\PropertyValidationFailedException
     * @throws Exceptions\Relation\ManyRelationInvalidException
     * @throws Exceptions\Relation\SingleRelationInvalidException
     * @throws PropertyNotRegisteredException
     * @throws PropertyValueInvalidException
     */
    public function __construct($attributes = null, $raw = false)
    {
        if ($attributes) {
            $raw ? $this->setRawAttributes($attributes) : $this->setAttributes($attributes);
        }
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws Exceptions\Property\PropertyValidationFailedException
     * @throws Exceptions\Relation\ManyRelationInvalidException
     * @throws Exceptions\Relation\SingleRelationInvalidException
     * @throws PropertyNotRegisteredException
     * @throws PropertyValueInvalidException
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
        if (!$this->query) {
            $this->query = $this->newQuery();
        }

        return $this->query;
    }

    /**
     * @return Builder
     */
    public function newQuery()
    {
        return new Builder($this);
    }

    /**
     * @param array $meta
     * @return Element
     */
    public function setMeta(array $meta)
    {
        // TODO: Make these an actual array or object

        if (isset($meta['id'])) {
            $this->primaryKey = $meta['id'];
        }

        if (isset($meta['created_at'])) {
            $this->createdAt = $meta['created_at'];
        }

        if (isset($meta['updated_at'])) {
            $this->updatedAt = $meta['updated_at'];
        }

        return $this;
    }

    /**
     * @return array
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    public function toJson()
    {
        return [
            'meta' => $this->getMeta(),
            'attributes' => $this->getAttributes(),
            'properties' => collect($this->getElementDefinition()->getPropertyDefinitions())->map->toJson()->all()
        ];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'id' => $this->primaryKey,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * @return ElementDefinition
     * @throws ElementNotRegisteredException
     * @throws ElementsNotInstalledException
     */
    public function getElementDefinition()
    {
        return elements()->getElementDefinition($this->getAlias());
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
        return Entity::findOrFail($this->getPrimaryKey());
    }

    /**
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
}
