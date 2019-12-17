<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Exceptions\AttributeSchema\AttributeSchemaClassInvalidException;
use Click\Elements\Exceptions\Relation\RelationTypeNotValidException;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Types\RelationType;

class RelatedElement extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws AttributeSchemaClassInvalidException
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     * @throws RelationTypeNotValidException
     */
    public function buildDefinition(ElementSchema $schema)
    {
        $schema->relation('plainElement', PlainElement::class, RelationType::SINGLE);
        $schema->relation('plainElements', PlainElement::class, RelationType::MANY);
        $schema->relation('relatedElement', RelatedElement::class, RelationType::SINGLE);
    }
}
