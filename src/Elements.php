<?php

namespace Click\Elements;

use Click\Elements\Exceptions\ElementClassInvalidException;
use Click\Elements\Exceptions\TablesMissingException;
use Click\Elements\Exceptions\ElementTypeNameInvalidException;
use Click\Elements\Exceptions\ElementTypeMissingException;
use Click\Elements\Exceptions\PropertyTypeInvalidException;

/**
 * Registers, instantiates and persists Elements.
 */
class Elements
{
    /** @var array */
    protected $elementTypes = [];

    /**
     * @param string $class
     * @return ElementType
     * @throws ElementTypeNameInvalidException
     */
    public function register(string $class)
    {
        $this->validateClass($class);

        $elementType = new ElementType($class);

        $this->validateTypeName($type = $elementType->getType());

        $this->elementTypes[$type] = $elementType;

        return $elementType;
    }


    /**
     * @param $type
     * @throws ElementTypeNameInvalidException
     */
    protected function validateTypeName($type)
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z_0-9]*$/', $type)) {
            throw new ElementTypeNameInvalidException($type);
        }
    }

    /**
     * @param $type
     * @throws ElementTypeMissingException
     */
    protected function validateType($type)
    {
        if (!isset($this->elementTypes[$type])) {
            throw new ElementTypeMissingException($type);
        }
    }

    /**
     * @throws Exception
     */
    public function install()
    {
        $this->checkTablesExist();

        foreach ($this->elementTypes as $type => $elementType) {
            $elementType->install();
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
     * @throws ElementClassInvalidException
     */
    protected function validateClass(string $class)
    {
        // TODO: Check EntityContract is implemented

        if (!is_subclass_of($class, Element::class)) {
            throw new ElementClassInvalidException($class);
        }
    }

    /**
     * @param $type
     * @param array $attributes
     * @return mixed
     * @throws ElementTypeMissingException
     */
    public function factory($type, $attributes = [])
    {
        $this->validateType($type);

        $elementType = $this->getElementType($type);

        return $elementType->factory($attributes);
    }

    /**
     * @param $type
     * @return ElementType
     * @throws ElementTypeMissingException
     */
    public function getElementType(string $type)
    {
        if (!isset($this->elementTypes[$type])) {
            throw new ElementTypeMissingException($type);
        }

        return $this->elementTypes[$type];
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
     * @throws TablesMissingException
     */
    protected function checkTablesExist()
    {
        $hasRun = \DB::table('migrations')->where('migration', '2019_09_01_082218_create_entities_table')->exists();

        if (!$hasRun) {
            throw new TablesMissingException;
        }
    }
}
