<?php

namespace Click\Elements\Exceptions\Attribute;

use Exception;

class AttributeValidationFailedException extends Exception
{
    /**
     * @var array
     */
    protected $errors;

    public function __construct(string $element, string $key, array $errors)
    {
        $this->errors = $errors;

        $errors = '"' . implode('", "', $errors) . '"';

        parent::__construct(sprintf('Attribute "%s" has failed validation on "%s" for element "%s".', $element, $errors, $key));
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
