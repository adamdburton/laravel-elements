<?php

namespace Click\Elements;

use Click\Elements\Elements\ElementType;
use Click\Elements\Exceptions\ElementClassInvalidException;
use Click\Elements\Exceptions\ElementTypeNotRegisteredException;
use Click\Elements\Exceptions\TablesMissingException;
use Illuminate\Support\Facades\Log;

/**
 * Registers, instantiates and persists Elements.
 */
class Elements
{
    /** @var ElementDefinition[] */
    protected $elementDefinitions = [];

    /**
     * @param string $class
     * @return ElementDefinition
     */
    public function register(string $class)
    {
        $definition = new ElementDefinition($class);

        $this->elementDefinitions[$definition->getClass()] = $definition;

        Log::debug('Registering element.', ['element' => $definition->getClass()]);

        return $definition;
    }

    /**
     * @param $type
     * @throws ElementTypeNotRegisteredException
     */
    protected function validateType($type)
    {
        if (!isset($this->elementDefinitions[$type])) {
            throw new ElementTypeNotRegisteredException($type);
        }
    }

    /**
     * @throws TablesMissingException
     */
    public function install()
    {
        $this->checkTablesExist();

        Log::debug('Registering and installing ElementType element.');

        $this->register(ElementType::class)->install();

        foreach ($this->getDefinitions() as $type => $definition) {
            Log::debug('Installing registered element.', ['element' => $definition->getClass()]);

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
        if (!isset($this->elementDefinitions[$type])) {
            throw new ElementTypeNotRegisteredException($type);
        }

        return $this->elementDefinitions[$type];
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
        return array_filter($this->elementDefinitions, function (ElementDefinition $definition) {
            return !in_array($definition->getClass(), [
                ElementType::class
            ]);
        });
    }
}
