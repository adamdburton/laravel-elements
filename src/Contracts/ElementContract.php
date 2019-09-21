<?php

namespace Click\Elements\Contracts;

use Click\Elements\Schema;

interface ElementContract
{
    public function getDefinition(Schema $schema);
}
