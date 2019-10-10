<?php

namespace Click\Elements\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot for storing entity properties
 */
class EntityProperty extends Pivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $table = 'elements_entity_properties';

    protected $dates = [
        'timestamp_value'
    ];

    protected $casts = [
        'boolean_value' => 'boolean',
        'integer_value' => 'integer',
        'unsigned_integer_value' => 'integer',
        'double_value' => 'double',
        'string_value' => 'string',
        'text_value' => 'string',
        'json_value' => 'array'
    ];
}
