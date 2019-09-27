<?php

namespace Click\Elements\Definitions;

use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Models\Property;
use Click\Elements\Schemas\PropertySchema;

/**
 * Property definition container
 */
class PropertyDefinition implements DefinitionContract
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $validation = [];

    public function __construct(array $schema)
    {
        $this->key = $schema['key'];
        $this->type = $schema['type'];
        $this->validation = $schema['validation'] ?? [];
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
        return Property::create(['key' => $this->getKey(), 'type' => $this->getType()]);
    }
}
