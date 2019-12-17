<?php

namespace Click\Elements;

use Click\Elements\Contracts\SchemaContract;

/**
 * A blueprint-like interface for defining Element attributes.
 */
abstract class Schema implements SchemaContract
{
    /**
     * @var array
     */
    protected $attributes = [];
}
