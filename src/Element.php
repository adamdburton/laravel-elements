<?php

namespace Click\Elements;

use Click\Elements\Concerns\HasTypedProperties;
use Click\Elements\Contracts\ElementContract;
use Click\Elements\Definitions\ElementDefinition;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * The base Element class. You should extend this.
 * @method static create(array $attributes)
 * @method static update(array $attributes)
 */
abstract class Element implements ElementContract
{
    use HasTypedProperties;
    use ForwardsCalls;

    /** @var int */
    public $primaryKey;

    /** @var string */
    protected $typeName;

    /** @var Builder */
    protected $query;

    /**
     * @param null $attributes
     * @param bool $raw
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
     */
    public function __call($method, $parameters)
    {
        if (!$this->query) {
            $this->query = $this->newQuery();
        }

        return $this->forwardCallTo($this->query, $method, $parameters);
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static())->$method(...$parameters);
    }

    /**
     * @return Builder
     */
    public function newQuery()
    {
        return new Builder($this);
    }

    /**
     * @param int $primaryKey
     * @return Element
     */
    public function setPrimaryKey(int $primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return string
     */
    public function getElementTypeName()
    {
        return $this->typeName ?: get_class($this);
    }

    /**
     * @return ElementDefinition
     * @throws Exceptions\ElementTypeNotRegisteredException
     */
    public function getElementDefinition()
    {
        /** @var Elements $elements */
        $elements = app(Elements::class);

        return $elements->getElementDefinition($this->getElementTypeName());
    }

    /**
     * @return mixed
     * @throws Exceptions\PropertyMissingException
     * @throws Exceptions\ElementTypeNotRegisteredException
     */
    public function getProperties()
    {
        return $this->getElementDefinition()->getProperties();
    }

    /**
     * @return Validator
     */
    public function validate()
    {
        return $this->query->validateWith($this->attributes);
    }

    /**
     * @return Collection
     */
    public function all()
    {
        return $this->newQuery()->get();
    }
}
