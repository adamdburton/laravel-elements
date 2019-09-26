<?php

namespace Click\Elements\Elements;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;

/**
 * Module Element definition.
 */
class ElementType extends Element
{
    /**
     * @param ElementSchema $schema
     */
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('name')->required();
        $schema->array('properties')->required();
    }
}
