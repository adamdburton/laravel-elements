<?php

namespace Click\Elements\Tests\Element;

use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\TestCase;

class ElementNotInstalledExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->elements->register(PlainElement::class);

        $this->expectException(ElementNotInstalledException::class);

        PlainElement::create([]);
    }
}
