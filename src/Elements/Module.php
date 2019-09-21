<?php

namespace Click\Elements\Elements;

use Click\Elements\Element;
use Click\Elements\Schema;

/**
 * Module Element definition.
 */
class Module extends Element
{
    public function getDefinition(Schema $schema)
    {
        $schema->string('name');
        $schema->string('description');
        $schema->string('version');
    }
}