<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Element;
use Click\Elements\Schemas\ElementSchema;

class DuplicateKeyElement extends Element
{
    public function getDefinition(ElementSchema $schema)
    {
        $schema->string('wefewf');
        $schema->string('wefewf');
    }
}
