<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;

class BadKeyNameElement extends Element
{
    public function buildDefinition(ElementSchema $schema)
    {
        $schema->string(' ');
        $schema->string('_');
        $schema->string('-Hello');
        $schema->string('__Hello');
        $schema->string('spaces and symbols are not allowed');
    }
}
