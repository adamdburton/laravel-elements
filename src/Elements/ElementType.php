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
            ->description('')
            ->required();

        $schema->array('properties')
            ->description('Holds the key to ID lookups for element properties.')
            ->required();
    }
}
