<?php

namespace Click\Elements\Schemas;

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
