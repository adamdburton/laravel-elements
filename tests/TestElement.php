<?php

namespace Click\Elements\Tests\Elements;

use Click\Elements\Element;
use Click\Elements\Schema;

class TestElement extends Element
{
    public function getDefinition(Schema $schema)
    {
        $schema->string('string')->required();
        $schema->boolean('boolean');
        $schema->integer('integer');
        $schema->double('double');
        $schema->text('text');
        $schema->array('array');
        $schema->json('json')->required();
        $schema->relation('relation');
        $schema->timestamp('timestamp');
    }
}