<?php

namespace Click\Elements\Tests\Exceptions\Property;

use Click\Elements\Exceptions\Property\PropertyAlreadyDefinedException;
use Click\Elements\Tests\Assets\DuplicateKeyElement;
use Click\Elements\Tests\TestCase;

class PropertyAlreadyDefinedExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->expectException(PropertyAlreadyDefinedException::class);

        $this->elements->register(DuplicateKeyElement::class);
    }
}
