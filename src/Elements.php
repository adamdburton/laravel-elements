<?php

namespace Click\Elements;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Exceptions\Element\ElementClassInvalidException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;

/**
 * Registers, instantiates and persists Elements.
 */
class Elements
{
    /**
     * @var ElementDefinition[]
     */
    protected $elementDefinitions = [];

    /**
     * @var array
     */
    protected $elementAliases = [];

    /**
     * @param string $class
     * @return ElementDefinition
     * @throws ElementClassInvalidException
     * @throws Exceptions\Attribute\AttributeAlreadyDefinedException
     * @throws Exceptions\Attribute\AttributeKeyInvalidException
     * @throws Exceptions\AttributeSchema\AttributeSchemaClassInvalidException
     */
    public function register(string $class)
    {
        $this->validateClass($class);

        $definition = $this->getDefinitionForClass($class);
        $alias = $definition->getAlias();

        $this->elementDefinitions[$class] = $definition;
        $this->elementAliases[$alias] = $class;

        return $definition;
    }

    /**
     * @param string $class
     * @throws ElementClassInvalidException
     */
    protected function validateClass(string $class)
    {
        if (!is_subclass_of($class, Element::class)) {
            throw new ElementClassInvalidException($class);
        }
    }

    /**
     * @param string $class
     * @return ElementDefinition
     * @throws Exceptions\Attribute\AttributeAlreadyDefinedException
     * @throws Exceptions\Attribute\AttributeKeyInvalidException
     * @throws Exceptions\AttributeSchema\AttributeSchemaClassInvalidException
     */
    protected function getDefinitionForClass(string $class)
    {
        return new ElementDefinition($class);
    }

    /**
     * @param $type
     * @return ElementDefinition
     * @throws ElementNotRegisteredException
     */
    public function getElementDefinition(string $type)
    {
        $type = $this->resolveType($type);

        return $this->elementDefinitions[$type];
    }

    /**
     * @param string $type
     * @return string
     * @throws ElementNotRegisteredException
     */
    protected function resolveType(string $type)
    {
        if (isset($this->elementAliases[$type])) {
            $type = $this->elementAliases[$type];
        }

        $this->validateType($type);

        return $type;
    }

    /**
     * @param $type
     * @throws ElementNotRegisteredException
     */
    public function validateType($type)
    {
        if (!isset($this->elementDefinitions[$type])) {
            throw new ElementNotRegisteredException($type);
        }
    }

    /**
     * @return ElementDefinition[]
     */
    public function getElementDefinitions()
    {
        return $this->elementDefinitions;
    }

    /**
     * @param ElementDefinition $definition
     * @return ElementDefinition
     */
    protected function assignDefinition(ElementDefinition $definition)
    {
        $this->elementDefinitions[$definition->getClass()] = $definition;
        $this->elementAliases[$definition->getAlias()] = $definition->getClass();

        return $definition;
    }
}
