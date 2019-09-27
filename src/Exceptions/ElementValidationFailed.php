<?php

namespace Click\Elements\Exceptions;

use Click\Elements\Element;
use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\MessageBag;

class ElementValidationFailed extends Exception
{
    /**
     * @var Element
     */
    protected $element;

    /**
     * @var Validator
     */
    protected $validator;

    public function __construct(Element $element, Validator $validator)
    {
        $this->element = $element;
        $this->validator = $validator;

        $failed = $validator->getMessageBag()->keys();

        $type = $element->getElementDefinition()->getClass();
        $properties = '"' . implode('", "', $failed) . '"';

        parent::__construct(sprintf('Element "%s" has failed validation on %s.', $type, $properties));
    }

    /**
     * @return MessageBag
     */
    public function getErrors()
    {
        return $this->validator->errors();
    }

    /**
     * @return Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }
}
