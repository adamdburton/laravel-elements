<?php

namespace Click\Elements\Tests\Assets;

use Click\Elements\Builder;
use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Schemas\ElementSchema;

class ScopedElement extends Element
{
    /**
     * @param ElementSchema $schema
     * @throws AttributeAlreadyDefinedException
     * @throws AttributeKeyInvalidException
     */
    public function buildDefinition(ElementSchema $schema)
    {
        $schema->boolean('enabled');
        $schema->string('status');
    }

    /**
     * @param Builder $query
     */
    public function scopeEnabled(Builder $query)
    {
        $query->where('enabled', true);
    }

    /**
     * @param Builder $query
     */
    public function scopeDisabled(Builder $query)
    {
        $query->where('enabled', false);
    }

    /**
     * @param Builder $query
     * @param string $status
     */
    public function scopeStatus(Builder $query, string $status)
    {
        $query->where('status', $status);
    }
}
