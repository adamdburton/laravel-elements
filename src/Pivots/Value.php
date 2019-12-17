<?php

namespace Click\Elements\Pivots;

use Click\Elements\Models\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot for storing entity properties
 */
class Value extends Pivot
{
    public $incrementing = true; // TODO: Check if required

    public $timestamps = false;

    protected $table = 'elements_values';

    protected $dates = [
        'timestamp_value'
    ];

    protected $casts = [
        'locale' => 'string',
        'boolean_value' => 'boolean',
        'integer_value' => 'integer',
        'unsigned_integer_value' => 'integer',
        'double_value' => 'double',
        'string_value' => 'string',
        'text_value' => 'string',
        'json_value' => 'array'
    ];

    // Relations

    /**
     * @return BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
