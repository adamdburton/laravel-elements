<?php

namespace Click\Elements\Contracts;

use Click\Elements\Schemas\Schema;

interface SchemaContract
{
    public function getSchema();

    /**
     * @param Schema $schema
     * @return void
     */
    public function getDefinition(Schema $schema);
}
