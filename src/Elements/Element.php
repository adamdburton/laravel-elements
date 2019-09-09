<?php

namespace Click\Elements\Elements;

use Click\Elements\PropertyType;
use Click\Elements\Schema;

class Element extends Schema
{
    /** @return array */
    public function getProperties()
    {
        return [
            'label' => [
                'type' => PropertyType::STRING
            ],
            'type' => [
                'type' => PropertyType::STRING
            ]
        ];
    }
}
