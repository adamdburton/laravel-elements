<?php

namespace Click\Elements\Elements;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;

/**
 * Element for element types
 */
class ElementType extends Element
{
    /**
     * @param ElementSchema $schema
     */
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('name')
            ->description('The Element class name.')
            ->required();

        $schema->array('properties')
            ->description('Holds data for element property lookups.')
            ->required();
    }
}
