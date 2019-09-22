<?php

namespace Click\Elements\Tests\Services;

use Click\Elements\Tests\Elements\TestElement;
use Click\Elements\Tests\TestCase;

class ElementTest extends TestCase
{
    public function test_create_element()
    {
        $element = TestElement::create([
            'string' => $string = 'some string',
            'integer' => $integer = 123456789,
            'array' => $array = ['some', 'array', 'data'],
        ]);

        $this->assertSame($string, $element->string);
        $this->assertSame($integer, $element->integer);
        $this->assertSame($array, $element->array);
    }

    public function test_update_element()
    {
        $element = TestElement::create([
            'string' => 'some string',
            'integer' => 123456789,
            'array' => ['some', 'array', 'data'],
        ]);

        $element = $element->update([
            'string' => $string = 'some new string',
            'integer' => $integer = 987654321,
            'array' => $array = ['some', 'thing', 'else'],
        ]);

        $this->assertSame($string, $element->string);
        $this->assertSame($integer, $element->integer);
        $this->assertSame($array, $element->array);
    }
}
