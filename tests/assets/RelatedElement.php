<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Types\RelationType;

class RelatedElement extends Element
{
    public $plainElement;

    public function getDefinition(ElementSchema $schema)
    {
        $schema->relation('plainElement', PlainElement::class, RelationType::SINGLE);
        $schema->relation('plainElements', PlainElement::class, RelationType::MANY);
        $schema->relation('relatedElement', RelatedElement::class, RelationType::SINGLE);
    }
}
