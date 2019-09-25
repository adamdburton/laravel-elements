<?php

namespace Click\Elements\Schemas;

use Click\Elements\Definitions\PropertyDefinition;

/**
 * Class ElementSchema
 */
class PropertySchema extends Schema
{
    /** @return string */
    public function getDefinitionClass()
    {
        return PropertyDefinition::class;
    }

    /**
     * @return PropertySchema
     */
    public function required()
    {
        $this->definition['required'] = true;

        return $this;
    }

    /**
     * @param $label
     * @return PropertySchema
     */
    public function label($label)
    {
        $this->definition['label'] = $label;

        return $this;
    }

    /**
     * @param array $rules
     * @return PropertySchema
     */
    public function validation(array $rules)
    {
        $this->definition['validation'] = $rules;

        return $this;
    }
}
