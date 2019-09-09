<?php

namespace Click\Elements\Tests\Services;

use Click\Elements\Elements\Element;
use Click\Elements\Exceptions\ElementTypeAlreadyExistsException;
use Click\Elements\Models\Entity;
use Click\Elements\Services\ElementService;
use Click\Elements\Tests\TestCase;

class ElementServiceTest extends TestCase
{
    /** @var ElementService */
    private $elements;

    public function setUp(): void
    {
        parent::setUp();

        $this->elements = elements()->elements();
    }

    public function test_factory()
    {
        $data = [
            'label' => 'Tests',
            'type' => 'test'
        ];

        $element = elements()->elements()->factory(Element::class, $data);

        $this->assertSame($data, $element->getAttributes());
    }

    public function test_cannot_register_same_type()
    {
        $this->expectException(ElementTypeAlreadyExistsException::class);

        $this->elements->register(Element::class);
    }

    public function test_create()
    {
        $data = [
            'label' => 'Tests',
            'type' => 'test'
        ];

        $element = elements()->elements()->factory(Element::class, $data);

        $this->assertSame($data, $element->getAttributes());

        $element = elements()->elements()->create($element);

        $this->assertEquals($element->id, Entity::first()->id);
    }

//    public function
}

//        $fef = [
//            'integer' => 123,
//            'double' => 123.45,
//            'string' => 'something',
//            'bool' => true,
//            'text' => 'Nulla vitae elit libero, a pharetra augue. Nullam quis risus eget urna mollis ornare vel eu leo. Maecenas sed diam eget risus varius blandit sit amet non magna. Donec ullamcorper nulla non metus auctor fringilla. Maecenas faucibus mollis interdum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aenean lacinia bibendum nulla sed consectetur. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Maecenas faucibus mollis interdum. Integer posuere erat a ante venenatis dapibus posuere velit aliquet.',
//            'array' => ['a', 'b', 'c'],
//            'json' => ['a' => 1, 'b' => 2, 'c' => 3],
//        ];