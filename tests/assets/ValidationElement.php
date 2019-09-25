<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Schema;

class ValidationElement extends Element
{
    public function getDefinition(Schema $schema)
    {
        $schema->string('string')->required();
        $schema->boolean('boolean')->required();
        $schema->integer('integer')->required();
        $schema->double('double')->required();
        $schema->text('text')->required();
        $schema->array('array')->required();
        $schema->json('json')->required();
        $schema->relation('relation')->required();
        $schema->timestamp('timestamp')->required();
    }
}