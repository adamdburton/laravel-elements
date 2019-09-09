<?php

namespace Click\Elements\Tests\Services;

use Click\Elements\Elements;
use Click\Elements\Elements\Element;
use Click\Elements\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_elements_path()
    {
        $path = elements_path();

        $this->assertEquals(realpath(__DIR__ . '/..'), $path);
    }

    public function test_elements()
    {
        $elements = elements();

        $this->assertSame(app(Elements::class), $elements);
    }

    public function test_element()
    {
        $element = element(Element::class, [
            'label' => 'Elements',
            'type' => 'element',
        ]);

        $this->assertEquals('element', $element->getEntityType());
    }
}