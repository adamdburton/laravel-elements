<?php

namespace Click\Elements;

use Click\Elements\Elements\ElementType;
use Click\Elements\Elements\TypedProperty;
use Click\Elements\Exceptions\ElementTypeNameInvalidException;
use Click\Elements\Exceptions\PropertyMissingException;
use Click\Elements\Exceptions\PropertyTypeInvalidException;
use Click\Elements\Models\Property;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class ElementType
 */
class ElementDefinition
{
    // type
    // class
    // definition
    // properties

    /** @var string */
    protected $type;

    /** @var string */
    protected $class;

    /** @var array */
    protected $definition;

    /** @var array */
    protected $properties;

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
     * @return string
     */
    public function getType()
    {
        if (!$this->type) {
            $this->type = $this->factory()->getElementTypeName();
        }

        return $this->type;
    }

    /**
     * @return array
     */
    protected function getDefinition()
    {
        if (!$this->definition) {
            /** @var Element $class */
            $this->factory()->getDefinition($schema = new Schema($this));

            $this->definition = $schema->getDefinition();

            $this->validateDefinition($this->definition);
        }

        return $this->definition;
    }

    /**
     * @param $properties
     */
    protected function validateDefinition($properties)
    {
        collect($properties)->each(function ($property, $key) {
            $this->validatePropertyName($key);
            $this->validatePropertyType($property->type);
        });
    }

    /**
     * @param $type
     * @throws ElementTypeNameInvalidException
     */
    protected function validatePropertyName($type)
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z_0-9]*$/', $type)) {
            throw new ElementTypeNameInvalidException($type);
        }
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

    /**
     * @return Elements\ElementType
     */
    public function install()
    {
        collect($this->getDefinition())->map(function ($typedProperty) {
            /** @var TypedProperty $typedProperty */
//            echo 'creating property ' . $typedProperty->key;
            return Property::create($typedProperty->toArray());
        })->all();

        return Elements\ElementType::create(['name' => $this->getType()]);
    }

    /**
     * @return array
     * @throws PropertyMissingException
     */
    public function getProperties()
    {
        if (!$this->properties) {
            $this->properties = collect($this->getDefinition())->map(function ($_, $key) {
                $prefixedKey = $this->prefixKey($key);

                try {
                    return Property::key($prefixedKey)->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    dd(Property::all());
                    throw new PropertyMissingException($prefixedKey);
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
        $property = str_replace($this->getType() . '.', '', $property);
        $properties = $this->getProperties();

        return $properties[$property] ?? null;
    }

    /**
     * @param $key
     * @return string
     */
    public function prefixKey($key)
    {
        return sprintf('%s.%s', $this->getType(), $key);
    }
}
