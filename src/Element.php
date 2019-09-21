<?php

namespace Click\Elements;

use Click\Elements\Concerns\HasTypedAttributes;
use Click\Elements\Contracts\ElementContract;
use Click\Elements\Elements\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * The base Element class. You should extend this.
 */
abstract class Element implements ElementContract
{
    use HasTypedAttributes;
    use ForwardsCalls;

    /** @var string */
    protected $typeName;

    /** @var Builder */
    protected $query;

    public function __construct($attributes = null)
    {
        if ($attributes) {
            $this->setAttributes($attributes);
        }
    }

    public function __call($method, $parameters)
    {
        if (!$this->query) {
            $this->query = new Builder($this);
        }

        return $this->forwardCallTo($this->query, $method, $parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * @return string
     */
    public function getElementTypeName()
    {
        return $this->typeName ?: lcfirst(Str::studly(class_basename($this)));
    }

    /**
     * @return ElementType
     * @throws \Exception
     */
    public function getElementType()
    {
        return app(Elements::class)->getElementType($this->getElementTypeName());
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getProperties()
    {
        return $this->getElementType()->getProperties();
    }
}
