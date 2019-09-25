<?php

namespace Click\Elements\Contracts;

use Click\Elements\Schemas\ElementSchema;

interface ElementContract
{
    public function getDefinition(ElementSchema $schema);
}
