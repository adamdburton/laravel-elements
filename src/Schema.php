<?php

namespace Click\Elements;

use Click\Elements\Contracts\SchemaContract;

/**
 * A blueprint-like interface for defining Element properties.
 */
abstract class Schema implements SchemaContract
{
    /**
     * @var array
     */
    protected $schema = [];
}
