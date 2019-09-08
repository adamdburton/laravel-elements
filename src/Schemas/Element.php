<?php

namespace Click\Elements\Schemas;

use Click\Elements\PropertyType;
use Click\Elements\Schema;

class Element extends Schema
{
    public function getEntityProperties()
    {
        return [
            'label' => PropertyType::STRING,
            'type' => PropertyType::STRING,
        ];
    }
}