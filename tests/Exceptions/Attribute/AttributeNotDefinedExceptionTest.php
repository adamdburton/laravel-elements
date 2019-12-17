<?php

namespace Click\Elements\Tests\Exceptions\Attribute;

use Click\Elements\Exceptions\Attribute\AttributeNotDefinedException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\TestCase;

class AttributeNotDefinedExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->elements->register(PlainElement::class);

        $element = new PlainElement();

        $this->expectException(AttributeNotDefinedException::class);

        $element->doesNotExist;
    }
}
