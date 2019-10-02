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

        $schema->string('alias')
            ->description('A short alias for the element type.')
            ->required();
    }
}
