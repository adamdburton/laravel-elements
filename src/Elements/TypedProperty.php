<?php

namespace Click\Elements\Elements;

use Click\Elements\Element;
use Click\Elements\Schema;

/**
 * Module Element definition.
 */
class TypedProperty extends Element
{
    /**
     * @param Schema $schema
     */
    public function getDefinition(Schema $schema)
    {
        $schema->string('label');
        $schema->string('key');
        $schema->string('type');
        $schema->boolean('required');
    }

    /**
     * @param $label
     * @return $this
     */
    public function label($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return $this
     */
    public function required()
    {
        $this->required = true;

        return $this;
    }
}
