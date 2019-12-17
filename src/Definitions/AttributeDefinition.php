<?php

namespace Click\Elements\Definitions;

use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Models\Attribute;
use Click\Elements\Schemas\AttributeSchema;

/**
 * Attribute definition container
 */
class AttributeDefinition implements DefinitionContract
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
     * @var AttributeSchema
     */
    protected $schema;

    /**
     * @param ElementDefinition $definition
     * @param AttributeSchema $schema
     */
    public function __construct(ElementDefinition $definition, AttributeSchema $schema)
    {
        $this->elementDefinition = $definition;

        $schema = $schema->getSchema();

        $this->key = $schema['key'];
        $this->type = $schema['type'];
        $this->meta = $schema['meta'];
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
     * @param null $key
     * @param null $default
     * @return mixed
     */
    public function getMeta($key = null, $default = null)
    {
        return $key ? ($this->meta[$key] ?? $default) : $this->meta;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return Attribute::where('key', $this->getKey())->where('type', $this->getType())->exists();
    }

    /**
     * @return Attribute
     */
    public function install()
    {
        return Attribute::create([
            'key' => $this->getKey(),
            'type' => $this->getType()
        ]);
    }
}
