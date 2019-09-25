<?php

namespace Click\Elements\Contracts;

interface DefinitionContract
{
    /** @return string */
    public function getSchema();

    /**
     * @param DefinitionContract $definition
     * @return void
     */
    public function validateDefinition(DefinitionContract $definition);
}
