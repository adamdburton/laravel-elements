<?php

namespace Click\Elements;

use Click\Elements\Concerns\HasTypedProperties;
use Click\Elements\Contracts\ElementContract;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * The base Element class. You should extend this.
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
     */
    public function __call($method, $parameters)
    {
        if (!$this->query) {
            $this->query = new Builder($this);
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
        return $this->typeName ?: lcfirst(Str::studly(class_basename($this)));
    }

    /**
     * @return ElementDefinition
     * @throws Exceptions\ElementTypeMissingException
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
     * @throws Exceptions\ElementTypeMissingException
     */
    public function getProperties()
    {
        return $this->getElementDefinition()->getProperties();
    }
}
