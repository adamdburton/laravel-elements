<?php

namespace Click\Elements\Schemas;

/**
 * A blueprint-like interface for defining Element properties.
 */
abstract class Schema
{
    /** @var array */
    protected $definition = [];

    /** @return string */
    abstract public function getDefinitionClass();

    /**
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }
}
