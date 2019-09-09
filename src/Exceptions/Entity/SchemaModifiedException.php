<?php

namespace Click\Elements\Exceptions\Entity;

use Exception;

class SchemaModifiedException extends Exception
{
    public function __construct($type, $migrationPath)
    {
        parent::__construct(sprintf('The schema for "%s" has been modified. A migration has been generated at %s. Run php artisan elements:migrate to update to the new schema.', $type, $migrationPath));
    }
}
