<?php

namespace Click\Elements\Benchmarks\Assets\Elements;

use Click\Elements\Element;
use Click\Elements\Exceptions\Property\PropertyAlreadyDefinedException;
use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Exceptions\Relation\RelationTypeNotValidException;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Types\RelationType;

class Book extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws PropertyKeyInvalidException
     * @throws PropertyAlreadyDefinedException
     * @throws RelationTypeNotValidException
     */
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('name');
        $schema->unsignedInteger('released');
        $schema->relation('author', Author::class, RelationType::SINGLE);
    }
}
