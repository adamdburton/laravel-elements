<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;

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
        $schema->relation('relation');
        $schema->timestamp('timestamp');
    }
}
