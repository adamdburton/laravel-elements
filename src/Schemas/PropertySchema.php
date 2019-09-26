<?php

namespace Click\Elements\Schemas;

use Click\Elements\Schema;

/**
 * Class ElementSchema
 */
class PropertySchema extends Schema
{
    /**
     * @param $key
     * @return PropertySchema
     */
    public function key($key)
    {
        $this->schema['key'] = $key;

        return $this;
    }

    /**
     * @param $type
     * @return PropertySchema
     */
    public function type($type)
    {
        $this->schema['type'] = $type;

        return $this;
    }

    /**
     * @return PropertySchema
     */
    public function required()
    {
        $this->schema['required'] = true;

        return $this;
    }

    /**
     * @param $label
     * @return PropertySchema
     */
    public function label($label)
    {
        $this->schema['label'] = $label;

        return $this;
    }

    /**
     * @param array $rules
     * @return PropertySchema
     */
    public function validation(array $rules)
    {
        $this->schema['validation'] = $rules;

        return $this;
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        // Move the required field to validation

        if (isset($this->schema['required'])) {
            $validation = $this->schema['validation'] ?? [];

            if (!in_array('required', $validation)) {
                $validation[] = 'required';
            }

            unset($this->schema['required']);
            $this->schema['validation'] = $validation;
        }

        return $this->schema;
    }
}
