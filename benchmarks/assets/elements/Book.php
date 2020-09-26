<?php

namespace Click\Elements\Benchmarks\Assets\Elements;

use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Exceptions\AttributeSchema\AttributeSchemaClassInvalidException;
use Click\Elements\Exceptions\Relation\RelationTypeNotValidException;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Types\RelationType;

class Book extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws AttributeKeyInvalidException
     * @throws AttributeAlreadyDefinedException
     * @throws RelationTypeNotValidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function buildDefinition(ElementSchema $schema)
    {
        $schema->string('name');
        $schema->unsignedInteger('released');
        $schema->relation('author', Author::class, RelationType::SINGLE);
    }
}
