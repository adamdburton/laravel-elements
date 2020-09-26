<?php

namespace Click\Elements\Concerns\Element;

use Click\Elements\Element;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Illuminate\Database\Eloquent\Model;

/**
 * Provides two way binding attributes and methods for Elements
 */
trait HasTwoWayBinding
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

    /**
     * @param Element $element
     * @return array
     * @throws ElementNotRegisteredException
     */
//    public static function mapForModel(Element $element)
//    {
//        return $element->toJson()['attributes'];
//    }

    /**
     * @param Model $model
     * @return array
     */
//    public static function mapForElement(Model $model)
//    {
//        return $model->toArray();
//    }

    /**
     * @return string
     */
    abstract public function getModel();
}
