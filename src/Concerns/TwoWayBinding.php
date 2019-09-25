<?php

namespace Click\Elements\Concerns;

use Click\Elements\Element;
use Illuminate\Database\Eloquent\Model;

trait TwoWayBinding
{
    /**
     * Whether to update the model when the element is updated.
     * @var bool
     */
    protected $syncToModel = false;

    /**
     * Whether to update the element when the model is updated.
     * @var bool
     */
    protected $syncFromModel = false;

    /** @return string */
    abstract public function getModel();

    /**
     * @param Element $element
     * @return array
     */
    public static function mapForModel(Element $element)
    {
        return $element->toArray();
    }

    /**
     * @param Model $model
     * @return array
     */
    public static function mapForElement(Model $model)
    {
        return $model->toArray();
    }

}
