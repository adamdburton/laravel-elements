<?php

namespace Click\Elements\Elements;

use Click\Elements\Element;
use Click\Elements\Exceptions\Property\PropertyAlreadyDefinedException;
use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Schemas\ElementSchema;

/**
 * Element for element types
 */
class ElementType extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws PropertyKeyInvalidException
     * @throws PropertyAlreadyDefinedException
     */
    public function getDefinition(ElementSchema $schema)
    {
        $schema
            ->string('class')
            ->label('Class')
            ->description('The Element class name.')
            ->required();
    }
}
