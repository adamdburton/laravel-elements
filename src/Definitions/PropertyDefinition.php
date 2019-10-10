<?php

namespace Click\Elements\Definitions;

use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Models\Property;
use Click\Elements\Schemas\PropertySchema;
use Click\Elements\Types\RelationType;

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
        return $this->schema->getSchema()['key'];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->schema->getSchema()['type'];
    }

    /**
     * @param null $key
     * @param null $default
     * @return mixed
     */
    public function getMeta($key = null, $default = null)
    {
        $meta = $this->schema->getSchema()['meta'];

        return $key ? ($meta[$key] ?? $default) : $meta;
    }

    public function getRelations($value)
    {
        $relationType = $this->getMeta('relationType');

        switch ($relationType) {
            case RelationType::BELONGS_TO:
            case RelationType::BELONGS_TO_MANY:
                return $value;
                break;
        }
    }

    /**
     * @return Property
     */
    public function install()
    {
        return Property::create([
            'element' => $this->elementDefinition->getAlias(),
            'key' => $this->getKey(),
            'type' => $this->getType()
        ]);
    }
}
