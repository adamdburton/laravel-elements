<?php

namespace Click\Elements\Definitions;

use Click\Elements\Exceptions\PropertyMissingException;
use Click\Elements\Models\Property;
use Click\Elements\Schemas\ElementSchema;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class ElementType
 */
class ElementDefinition extends Definition
{
    /** @var string */
    protected $class;

    /** @var array */
    protected $properties;

    /** @return string */
    public function getSchema()
    {
        return ElementSchema::class;
    }

    /**
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param array $attributes
     * @param null $primaryKey
     * @return Element
     */
    public function factory($attributes = [], $primaryKey = null)
    {
        $class = $this->class;

        /** @var Element $instance */
        $instance = new $class($attributes);

        if ($primaryKey) {
            $instance->setPrimaryKey($primaryKey);
        }

        return $instance;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
    /**
     * @param $properties
     */
    public function validateDefinition($properties)
    {
        // ???
    }

    /**
     * @return Elements\ElementType
     */
    public function install()
    {
        $definition = collect($this->getDefinition())->each(function (PropertyDefinition $definition) {
            // Install each Property model first
            $definition->install();
        })->map(function (PropertyDefinition $definition) {
            // Then return all the attributes that define them, to store in the Element
            return $definition->getAttributes();
        })->all();

        return Elements\ElementType::create([
            'name' => $this->getType(),
            'definition' => $definition
        ]);
    }

    /**
     * @return array
     * @throws PropertyMissingException
     */
    public function getProperties()
    {
        if (!$this->properties) {
            $this->properties = collect($this->getDefinition())->map(function (PropertyDefinition $propertyDefinition) {
                try {
                    return Property::fromDefinition($propertyDefinition)->firstOrFail();
                } catch (ModelNotFoundException $e) {
//                    dd(Property::all());
                    throw new PropertyMissingException($propertyDefinition);
                }
            })->all();
        }

        return $this->properties;
    }

    /**
     * @param $property
     * @return Property
     * @throws PropertyMissingException
     */
    public function getProperty($property)
    {
        $properties = $this->getProperties();

        return $properties[$property] ?? null;
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return collect($this->getDefinition())->map(function (PropertyDefinition $propertyDefinition) {
            return $propertyDefinition->getValidationRules(); // TODO: Fix this
        })->all();
    }
}
