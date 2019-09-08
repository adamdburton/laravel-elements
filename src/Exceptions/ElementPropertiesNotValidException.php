<?php

namespace Click\Elements\Exceptions;

use Exception;
use Illuminate\Contracts\Validation\Validator;

class ElementPropertiesNotValidException extends Exception
{
    /**
     * @var Validator
     */
    protected $validator;

    /** @var array */
    protected $properties;

    public function __construct($properties, Validator $validator)
    {
        parent::__construct('The given data was invalid.');

        $this->properties = $properties;
        $this->validator = $validator;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getValidator()
    {
        return $this->validator;
    }

}
