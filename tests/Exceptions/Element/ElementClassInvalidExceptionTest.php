<?php

namespace Click\Elements\Tests\Element;

use Click\Elements\Exceptions\Element\ElementClassInvalidException;
use Click\Elements\Tests\Assets\NotAnElement;
use Click\Elements\Tests\TestCase;

/**
 * @covers \Click\Elements\Element
 */
class ElementClassInvalidExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->expectException(ElementClassInvalidException::class);

        $this->elements->register(NotAnElement::class);
    }
}
