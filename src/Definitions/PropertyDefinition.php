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
    protected $meta = [];

    /**
     * @var ElementDefinition
     */
    protected $elementDefinition;

    /**
     * @var PropertySchema
     */
    protected $schema;

    /**
     * @param ElementDefinition $definition
     * @param PropertySchema $schema
     */
    public function __construct(ElementDefinition $definition, PropertySchema $schema)
    {
        $this->elementDefinition = $definition;

        $this->schema = $schema;
    }

    /**
     * @return ElementDefinition
     */
    public function getElementDefinition()
    {
        return $this->elementDefinition;
    }

    /**
     * @return array
     */
    public function toJson()
    {
        return [
            'key' => $this->getKey(),
            'type' => $this->getType(),
            'meta' => $this->getMeta()
        ];
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->schema->getKey();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->schema->getType();
    }

    /**
     * @param null $key
     * @param null $default
     * @return array
     */
    public function getMeta($key = null, $default = null)
    {
        $meta = $this->schema->getMeta();

        return $key ? ($meta[$key] ?? $default) : $meta;
    }

    /**
     * @return Property
     */
    public function install()
    {
        return Property::create(['element' => $this->elementDefinition->getAlias(), 'key' => $this->getKey(), 'type' => $this->getType()]);
    }
}
