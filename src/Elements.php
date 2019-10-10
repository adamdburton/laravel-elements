<?php

namespace Click\Elements;

use Click\Elements\Definitions\ElementDefinition;
use Click\Elements\Elements\ElementType;
use Click\Elements\Exceptions\Element\ElementClassInvalidException;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Exceptions\TablesMissingException;
use Click\Elements\Schemas\ElementSchema;
use Illuminate\Support\Facades\DB;
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
     * @throws Exceptions\Property\PropertyKeyInvalidException
     * @throws TablesMissingException
     * @throws Exceptions\Element\ElementNotInstalledException
     */
    public function install()
    {
        $this->checkTablesExist();

        Log::debug('Registering and installing ElementType element.');

        $this->register(ElementType::class)->install();

        foreach ($this->getDefinitions() as $definition) {
            Log::debug('Installing registered element.', ['element' => $definition->getClass()]);

            if (!$definition->isInstalled()) {
                $definition->install();
            }
        }
    }

    /**
     * @throws TablesMissingException
     */
    protected function checkTablesExist()
    {
        $hasRun = DB::table('migrations')->where('migration', '2019_09_01_082218_create_entities_table')->exists();

        if (!$hasRun) {
            throw new TablesMissingException();
        }
    }

    /**
     * @param string $class
     * @return ElementDefinition
     * @throws ElementClassInvalidException
     * @throws Exceptions\Property\PropertyKeyInvalidException
     */
    public function register(string $class)
    {
        $this->validateClass($class);

        $element = new $class;
        $element->getDefinition($schema = new ElementSchema());

        $definition = new ElementDefinition($element, $schema, false);

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
     * @return ElementDefinition[]
     */
    protected function getDefinitions()
    {
        return $this->elementDefinitions;
    }

    /**
     * @return bool
     * @throws ElementNotRegisteredException
     * @throws TablesMissingException
     */
    public function isInstalled()
    {
        return $this->checkTablesExist() && $this->getElementDefinition('elementType') !== null;
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
     * @param array $attributes
     * @param array $meta
     * @param null $relations
     * @return Element
     * @throws ElementNotRegisteredException
     */
    public function factory($type, $attributes = null, $meta = null, $relations = null)
    {
        $this->resolveType($type);

        return $this->getElementDefinition($type)->factory($attributes, $meta, $relations);
    }

    /**
     * @return ElementDefinition[]
     */
    public function getElementDefinitions()
    {
        return $this->elementDefinitions;
    }
}
