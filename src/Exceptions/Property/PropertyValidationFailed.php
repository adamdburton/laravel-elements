<?php

namespace Click\Elements\Exceptions\Property;

use Exception;

class PropertyValidationFailed extends Exception
{
    /**
     * @var array
     */
    protected $errors;

    public function __construct(string $element, string $key, array $errors)
    {
        $this->errors = $errors;

        $properties = '"' . implode('", "', $errors) . '"';

        parent::__construct(sprintf('Element "%s" has failed validation on "%s" (%s).', $element, $key, $properties));
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
