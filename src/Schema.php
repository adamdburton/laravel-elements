<?php

namespace Click\Elements;

use Click\Elements\Elements\Module;

/**
 * A blueprint-like interface for defining Element properties.
 * @see Module for an example
 */
class Schema
{
    protected $definition = [];

    public function getDefinition()
    {
        return $this->definition;
    }

    public function boolean($key)
    {
        $this->definition[$key] = PropertyType::BOOLEAN;
    }

    public function integer($key)
    {
        $this->definition[$key] = PropertyType::INTEGER;
    }

    public function double($key)
    {
        $this->definition[$key] = PropertyType::DOUBLE;
    }

    public function string($key)
    {
        $this->definition[$key] = PropertyType::STRING;
    }

    public function text($key)
    {
        $this->definition[$key] = PropertyType::TEXT;
    }

    public function array($key)
    {
        $this->definition[$key] = PropertyType::ARRAY;
    }

    public function json($key)
    {
        $this->definition[$key] = PropertyType::JSON;
    }

    public function relation($key)
    {
        $this->definition[$key] = PropertyType::RELATION;
    }

    public function timestamp($key)
    {
        $this->definition[$key] = PropertyType::TIMESTAMP;
    }
}