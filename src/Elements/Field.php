<?php

namespace Click\Elements\Elements;

use Click\Elements\Entity;
use Click\Elements\PropertyType;

class Field extends Entity
{
    /**
     * @return array
     */
    public function getAttributes()
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