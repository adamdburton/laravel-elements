<?php

namespace Click\Elements\Schemas;

/**
 * Class ElementSchema
 */
class RelationSchema extends AttributeSchema
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

    /**
     * @param $key
     * @return $this
     */
    public function withPivot($key)
    {
        $pivots = $this->meta['pivots'] ?? [];

        $pivots[] = $key;

        $this->meta['pivots'] = $pivots;

        return $this;
    }
}
