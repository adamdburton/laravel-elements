<?php

namespace Click\Elements\Contracts;

use Click\Elements\Schemas\ElementSchema;

/**
 * Contract for Elements to implement
 */
interface ElementContract
{
    /**
     * @param ElementSchema $schema
     * @return void
     */
    public function buildDefinition(ElementSchema $schema);
}
