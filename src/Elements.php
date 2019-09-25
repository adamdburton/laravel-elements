<?php

namespace Click\Elements;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Elements\ElementType;
use Click\Elements\Elements\TypedProperty;
use Click\Elements\Exceptions\ElementClassInvalidException;
use Click\Elements\Exceptions\ElementTypeNotRegisteredException;
use Click\Elements\Exceptions\TablesMissingException;

/**
 * Registers, instantiates and persists Elements.
 */
class Elements
{
    /** @var ElementDefinition[] */
    protected $definitions = [];

    /**
     * @param string $class
     * @return ElementDefinition
     * @throws ElementClassInvalidException
     */
    public function register(string $class)
    {
        $definition = $this->createDefinition($class);

        $this->definitions[$definition->getType()] = $definition;

        return $definition;
    }

    /**
     * @param string $class
     * @return ElementDefinition
     * @throws ElementClassInvalidException
     */
    protected function createDefinition(string $class)
    {
        $this->validateClass($class);

        $definition = new ElementDefinition($class);
        $type = $definition->getType();

        return $definition;
    }

    /**
     * @param $type
     * @throws ElementTypeNotRegisteredException
     */
    protected function validateType($type)
    {
        if (!isset($this->definitions[$type])) {
            throw new ElementTypeNotRegisteredException($type);
        }
    }

    /**
     * @throws ElementClassInvalidException
     * @throws TablesMissingException
     */
    public function install()
    {
        $this->checkTablesExist();

//        Property::create(['key' => 'elementType.name', 'type' => 'string']);

        $this->register(ElementType::class)->install();
        $this->register(TypedProperty::class)->install();

        foreach ($this->getDefinitions() as $type => $definition) {
            $definition->install();
        }
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
     * @return Element
     * @throws ElementTypeNotRegisteredException
     */
    public function factory($type, $attributes = [])
    {
        $this->validateType($type);

        return $this->getElementDefinition($type)->factory($attributes);
    }

    /**
     * @param $type
     * @return ElementDefinition
     * @throws ElementTypeNotRegisteredException
     */
    public function getElementDefinition(string $type)
    {
        if (!isset($this->definitions[$type])) {
            throw new ElementTypeNotRegisteredException($type);
        }

        return $this->definitions[$type];
    }

    /**
     * @throws TablesMissingException
     */
    protected function checkTablesExist()
    {
        $hasRun = \DB::table('migrations')->where('migration', '2019_09_01_082218_create_entities_table')->exists();

        if (!$hasRun) {
            throw new TablesMissingException();
        }
    }

    /**
     * @return ElementDefinition[]
     */
    protected function getDefinitions()
    {
        return array_filter($this->definitions, function (ElementDefinition $definition) {
            return !in_array($definition->getClass(), [
                ElementType::class,
                TypedProperty::class
            ]);
        });
    }
}
