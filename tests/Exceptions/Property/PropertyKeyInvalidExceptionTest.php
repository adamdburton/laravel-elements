<?php

namespace Click\Elements\Tests\Exceptions\Property;

use Click\Elements\Exceptions\Property\PropertyAlreadyDefinedException;
use Click\Elements\Exceptions\Property\PropertyKeyInvalidException;
use Click\Elements\Tests\Assets\BadKeyNameElement;
use Click\Elements\Tests\Assets\DuplicateKeyElement;
use Click\Elements\Tests\TestCase;

class PropertyKeyInvalidExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->expectException(PropertyKeyInvalidException::class);

        $this->elements->register(BadKeyNameElement::class);
    }
}
