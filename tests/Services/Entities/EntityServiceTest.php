<?php

namespace Click\Elements\Tests\Services;

use Click\Elements\Schemas\Element;
use Click\Elements\Tests\TestCase;

class EntityServiceTest extends TestCase
{
    public function test_element_factory()
    {
        elements()->entities()->register(Element::class);

        $element = elements()->entities()->factory(new Element(), [
            'label' => 'Channels',
            'type' => 'channel'
        ]);

        elements()->entities()->save($element);
    }


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