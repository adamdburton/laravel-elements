<?php

namespace Click\Elements\Exceptions\Element;

use Click\Elements\Element;
use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\MessageBag;

class ElementValidationFailed extends Exception
{

    /**
     * @var Validator
     */
    protected $validator;

    public function __construct(string $element, Validator $validator)
    {
        $this->validator = $validator;

        $failed = $validator->getMessageBag()->keys();

        $properties = '"' . implode('", "', $failed) . '"';

        parent::__construct(sprintf('Element "%s" has failed validation on %s.', $element, $properties));
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
