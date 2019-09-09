<?php

namespace Click\Elements\Elements;

use Click\Elements\PropertyType;
use Click\Elements\Schema;

class Field extends Schema
{
    /** @return array */
    public function getProperties()
    {
        return [
            'group' => [
                'type' => PropertyType::RELATION,
                'required' => true,
                'filter' => [
                    'element' => FieldGroup::class
                ],
            ],
            'name' => [
                'type' => PropertyType::STRING,
                'required' => true,
                'validation' => [
                    'min' => 3,
                    'max' => 128
                ]
            ],
            'type' => [
                'type' => PropertyType::RELATION,
                'required' => true,
                'filter' => [
                    'element' => FieldType::class
                ],
            ],
            'rules' => [
                'type' => PropertyType::ARRAY
            ],
            'instructions' => [
                'type' => PropertyType::TEXT,
                'validation' => [
                    'max' => 128
                ]
            ],
        ];
    }
}
