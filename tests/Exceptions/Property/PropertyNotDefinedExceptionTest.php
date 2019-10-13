<?php

namespace Click\Elements\Tests\Exceptions\Property;

use Click\Elements\Exceptions\Property\PropertyNotDefinedException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\TestCase;

class PropertyNotDefinedExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->elements->register(PlainElement::class);

        $element = new PlainElement();

        $this->expectException(PropertyNotDefinedException::class);

        $element->doesNotExist;
    }
}
