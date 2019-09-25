<?php

namespace Click\Elements\Definitions;

use Click\Elements\Contracts\DefinitionContract;
use Click\Elements\Schemas\Schema;

abstract class Definition implements DefinitionContract
{
    /** @var array */
    protected $definition;

    /**
     * @param null $attributes
     * @return Schema
     */
    protected function makeSchema($attributes = null)
    {
        $schema = $this->getSchema();

        return new $schema($attributes);
    }

    /**
     * @return Definition[]
     */
    protected function getDefinition()
    {
        if (!$this->definition) {
            $this->factory()->getDefinition($schema = $this->makeSchema());

            $this->definition = $schema->getDefinition();

            $this->validateDefinition($this->definition);
        }

        return $this->definition;
    }
}
