<?php

namespace Click\Elements\Schemas;

use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Schema;

/**
 * Class ElementSchema
 */
class RelationPropertySchema extends PropertySchema
{
    /**
     * @param string $elementType
     * @return $this
     */
    public function elementType(string $elementType)
    {
        $this->meta['elementType'] = $elementType;

        return $this;
    }

    /**
     * @param string $relationType
     * @return $this
     */
    public function relationType(string $relationType)
    {
        $this->meta['relationType'] = $relationType;

        return $this;
    }
}
