<?php

namespace Click\Elements\Concerns\Element;

/**
 * Trait HasMeta
 */
trait HasMeta
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType()
        ];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
