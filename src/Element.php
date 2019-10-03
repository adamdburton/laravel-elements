<?php

namespace Click\Elements;

use Carbon\Carbon;
use Click\Elements\Concerns\HasTypedProperties;
use Click\Elements\Contracts\ElementContract;
use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyNotRegisteredException;
use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * The base Element class. You should extend this
 *
 * @method static Element create(array $attributes)
 * @method static Element update(array $attributes)
 * @see Builder::create()
 * @see Builder::update()
 */
abstract class Element implements ElementContract
{
    use HasTypedProperties;
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
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     */
    public function __call($method, $parameters)
    {
        if (!$this->query) {
            $this->query = $this->query();
        }

        return $this->forwardCallTo($this->query, $method, $parameters);
    }

    /**
     * @return Builder
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     */
    public function query()
    {
        return new Builder($this);
    }

    /**
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Carbon
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param array $meta
     * @return Element
     */
    public function setMeta(array $meta)
    {
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
     * @return string
     */
    public function getAlias()
    {
        return $this->aliasName ?: Str::camel(class_basename($this));
    }

    /**
     * @return Validator
     * @throws ElementNotRegisteredException
     */
    public function validate()
    {
        return $this->query->validateWith($this->attributes);
    }

    /**
     * @return Element[]
     * @throws ElementNotInstalledException
     * @throws ElementNotRegisteredException
     */
    public function all()
    {
        return $this->query()->get();
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
            'properties' => collect($this->getElementDefinition()->getProperties())->map->toJson()
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
        return elements()->getElementDefinition($this->getElementTypeName());
    }

    /**
     * @return string
     */
    public function getElementTypeName()
    {
        return $this->typeName ?: get_class($this);
    }
}
