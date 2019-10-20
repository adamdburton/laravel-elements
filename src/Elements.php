<?php

namespace Click\Elements;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Elements\ElementType;
use Click\Elements\Exceptions\Element\ElementClassInvalidException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Schemas\ElementSchema;
use Illuminate\Support\Facades\Log;

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
     * @throws ElementClassInvalidException
     */
    public function install()
    {
        Log::debug('Registering and installing ElementType element.');

        $this->register(ElementType::class)->install();
    }

    /**
     * @param string $class
     * @return ElementDefinition
     * @throws ElementClassInvalidException
     */
    public function register(string $class)
    {
        $this->validateClass($class);

        /** @var Element $element */
        $element = new $class;
        $element->getDefinition($schema = new ElementSchema($element));

        $definition = new ElementDefinition($element, $schema);

        $this->elementDefinitions[$definition->getClass()] = $definition;
        $this->elementAliases[$definition->getAlias()] = $definition->getClass();

        Log::debug('Registering element.', ['element' => $definition->getClass()]);

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
     * @param $type
     * @param array $attributes
     * @param array $meta
     * @return Element
     * @throws ElementNotRegisteredException
     */
    public function make($type, $attributes = null, $meta = null)
    {
        $this->resolveType($type);

        return $this->getElementDefinition($type)->make($attributes, $meta);
    }

    /**
     * @param string $type
     * @return string
     * @throws ElementNotRegisteredException
     */
    public function resolveType(string $type)
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
     * @return ElementDefinition[]
     */
    public function getElementDefinitions()
    {
        return $this->elementDefinitions;
    }
}
