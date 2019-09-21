<?php

namespace Click\Elements;

/**
 * Registers, instantiates and persists Elements.
 */
class Elements
{
    /** @var array */
    protected $elementTypes = [];

    /**
     * @param string $class
     * @throws Exception
     */
    public function register(string $class)
    {
        $this->validateClass($class);

        $elementType = new ElementType($class);

        $this->validateTypeName($type = $elementType->getType());

        $this->elementTypes[$type] = $elementType;
    }


    /**
     * @param $type
     * @throws Exception
     */
    protected function validateTypeName($type)
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z_0-9]*$/', $type)) {
            throw new Exception('Type must be a suitable format.');
        }
    }

    /**
     * @param $type
     * @throws Exception
     */
    protected function validateType($type)
    {
        if (!isset($this->elementTypes[$type])) {
            throw new Exception('Type must be a suitable format.');
        }
    }

    public function install()
    {
        foreach ($this->elementTypes as $type => $elementType) {
            /** @var ElementType $elementType */
            if(!\Click\Elements\Elements\ElementType::where('type', $type)->exists()) {
                $elementType->install();
            }
        }
    }

    /**
     * @param $properties
     */
    protected function validateDefinition($properties)
    {
        collect($properties)->each(function ($property, $key) {
            $this->validateTypeName($key);
            $this->validatePropertyType($property);
        });
    }

    /**
     * @param string $class
     * @throws Exception
     */
    protected function validateClass(string $class)
    {
        // TODO: Check EntityContract is implemented

        $class = class_basename($class);

        if (!preg_match('/^[a-zA-Z][a-zA-Z_0-9]*$/', $class)) {
            throw new Exception('Class name must be a suitable format.');
        }
    }

    /**
     * @param $type
     * @param array $attibutes
     * @return mixed
     * @throws Exception
     */
    public function factory($type, $attibutes = [])
    {
        $this->validateType($type);

        $elementType = $this->getElementType($type);

        return $elementType->factory($attibutes);
    }

    /**
     * @param $type
     * @return ElementType
     * @throws Exception
     */
    public function getElementType(string $type)
    {
        if (!isset($this->elementTypes[$type])) {
            throw new Exception('Element Type ' . $type . ' is not defined.');
        }

        return $this->elementTypes[$type];
    }

    /**
     * @param $type
     * @throws Exception
     */
    protected function validatePropertyType($type)
    {
        if (!in_array($type, PropertyType::getTypes())) {
            throw new Exception('Must be a valid property type: ' . $type);
        }
    }
}
