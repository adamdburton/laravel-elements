<?php

namespace Click\Elements;

use Click\Elements\Models\Property;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ElementType
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

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param array $attributes
     * @return Element
     */
    public function factory($attributes = [])
    {
        $class = $this->class;

        return new $class($attributes);
    }

    public function getType()
    {
        if (!$this->type) {
            $this->type = $this->factory()->getElementTypeName();
        }

        return $this->type;
    }

    protected function getDefinition()
    {
        if (!$this->definition) {
            /** @var Element $class */
            $this->factory()->getDefinition($schema = new Schema);

            $this->definition = $schema->getDefinition();

//            $this->validateDefinition($this->definition);
        }

        return $this->definition;
    }

    public function install()
    {
        collect($this->getDefinition())->map(function ($propertyType, $property) {
            return Property::create(
                [
                    'property' => $this->getType() . '.' . $property,
                    'type' => $propertyType
                ]
            );
        })->all();

        \Click\Elements\Elements\ElementType::create(['type' => $this->getType()]);
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        if (!$this->properties) {
            $this->properties = collect($this->getDefinition())->map(function ($_, $property) {
                $prefixedProperty = $this->prefixProperty($property);
                try {
                    return Property::property($prefixedProperty)->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    throw new Exception('Missing property: ' . $prefixedProperty);
                }
            })->all();
        }

        return $this->properties;
    }

    public function getProperty($property)
    {
        $properties = $this->getProperties();

        return $properties[$property] ?? null;
    }

    public function prefixProperty($property)
    {
        return sprintf('%s.%s', $this->getType(), $property);
    }
}
