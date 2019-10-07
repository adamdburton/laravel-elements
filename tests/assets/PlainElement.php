<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Types\RelationType;

class PlainElement extends Element
{
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('string');
        $schema->boolean('boolean');
        $schema->integer('integer');
        $schema->double('double');
        $schema->text('text');
        $schema->array('array');
        $schema->json('json');
        $schema->relation('relation', 'test', RelationType::SINGLE);
        $schema->timestamp('timestamp');
    }
}
