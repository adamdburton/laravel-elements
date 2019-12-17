<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Exceptions\AttributeSchema\AttributeSchemaClassInvalidException;
use Click\Elements\Exceptions\Relation\RelationTypeNotValidException;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Types\RelationType;

class PlainElement extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws RelationTypeNotValidException
     * @throws AttributeSchemaClassInvalidException
     */
    public function buildDefinition(ElementSchema $schema)
    {
        $schema->string('string');
        $schema->boolean('boolean');
        $schema->integer('integer');
        $schema->unsignedInteger('unsigned_integer');
        $schema->double('double');
        $schema->text('text');
        $schema->array('array');
        $schema->json('json');
        $schema->relation('relation', PlainElement::class, RelationType::SINGLE);
        $schema->timestamp('timestamp');
    }
}
