<?php

namespace Click\Elements\Elements;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;

class TypedProperty extends Element
{
    /**
     * @param ElementSchema $schema
     */
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('label');
        $schema->string('key');
        $schema->string('type');
        $schema->boolean('required');
    }
}
