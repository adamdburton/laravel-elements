<?php

namespace Click\Elements\Tests\Element;

use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\TestCase;

/**
 * @covers \Click\Elements\Element
 */
class ElementNotRegisteredExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->expectException(ElementNotRegisteredException::class);

        PlainElement::create([]);
    }
}
