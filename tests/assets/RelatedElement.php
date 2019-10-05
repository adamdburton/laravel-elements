<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Types\RelationType;

class RelatedElement extends Element
{
    public function getDefinition(ElementSchema $schema)
    {
        $schema->relation('plainElement', PlainElement::class, RelationType::BELONGS_TO);
        $schema->relation('plainElements', PlainElement::class, RelationType::BELONGS_TO_MANY);
        $schema->relation('relatedElement', RelatedElement::class, RelationType::BELONGS_TO);
    }
}
