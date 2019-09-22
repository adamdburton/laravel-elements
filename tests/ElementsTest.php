<?php

namespace Click\Elements\Tests;

use Click\Elements\Tests\Elements\TestElement;

class ElementsTest extends TestCase
{
    protected $testElementInstalled = false;

    public function test_register()
    {
        $elementType = $this->elements->register(TestElement::class);

        $this->assertSame('testElement', $elementType->getType());
    }

    public function test_install()
    {
        $elementType = $this->elements->register(TestElement::class);

        $elementType = $elementType->install();

        $this->assertSame('elementType', $elementType->getElementTypeName());
    }
}
