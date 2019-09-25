<?php

namespace Click\Elements\Definitions;

use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Elements\TypedProperty;
use Click\Elements\Exceptions\PropertyTypeInvalidException;
use Click\Elements\Models\Property;
use Click\Elements\PropertyType;
use Click\Elements\Schemas\PropertySchema;

class PropertyDefinition extends Definition
{
    /** @var string */
    protected $key;

    /** @var string */
    protected $type;

    /** @return string */
    public function getSchema()
    {
        return PropertySchema::class;
    }

    /**
     * @param string $key
     * @param string $type
     * @throws PropertyTypeInvalidException
     */
    public function __construct(string $key, string $type)
    {
        $this->validatePropertyType($type);

        $this->key = $key;
        $this->type = $type;
    }

    /**
     * @param $type
     * @throws PropertyTypeInvalidException
     */
    protected function validatePropertyType($type)
    {
        if (!PropertyType::isValidType($type)) {
            throw new PropertyTypeInvalidException($type);
        }
    }

    public function install()
    {
        Property::create([
            'key' => $this->key,
            'type' => $this->type
        ]);

        TypedProperty::create([]);
    }

    /**
     * @param DefinitionContract $definition
     * @return void
     */
    public function validateDefinition(PropertyDefinition $definition)
    {

    }
}