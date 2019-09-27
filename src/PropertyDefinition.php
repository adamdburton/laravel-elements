<?php

namespace Click\Elements;

use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Models\Property;

/**
 * Property definition container
 */
class PropertyDefinition implements DefinitionContract
{
    /** @var array */
    protected $definition;

    /** @var string */
    protected $key;

    /** @var string */
    protected $type;

    /** @var array */
    protected $validation = [];

    public function __construct(array $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * @return Property
     */
    public function install()
    {
        return Property::create(collect($this->definition)->only('key', 'type')->all());
    }
}
