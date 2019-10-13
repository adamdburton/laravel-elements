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
        $schema->json('json');
    }
}