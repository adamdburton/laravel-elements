<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;
use Click\Elements\Types\RelationType;

class ValidationElement extends Element
{
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('string')->validation('required');
        $schema->boolean('boolean')->validation('required');
        $schema->integer('integer')->validation('required');
        $schema->double('double')->validation('required');
        $schema->text('text')->validation('required');
        $schema->array('array')->validation('required');
        $schema->json('json')->validation('required');
        $schema->relation('relation', 'test', RelationType::SINGLE)->validation('required');
        $schema->timestamp('timestamp')->validation('required');
    }
}