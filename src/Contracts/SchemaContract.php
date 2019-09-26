<?php

namespace Click\Elements\Contracts;

use Click\Elements\Schemas\PropertySchema;
use Click\Elements\Schemas\Schema;

interface SchemaContract
{
    /**
     * @return array
     */
    public function getSchema();
}
