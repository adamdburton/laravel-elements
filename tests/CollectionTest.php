<?php

namespace Click\Elements\Tests;

use Click\Elements\Collection;
use Click\Elements\Tests\Assets\PlainElement;

class CollectionTest extends TestCase
{
    public function test_get_element_type()
    {
        $this->elements->register(PlainElement::class)->install();

        $plainElement = PlainElement::create([]);

        /** @var Collection $collection */
        $collection = PlainElement::all();

//        dd($collection);

        $this->assertEquals($plainElement->getElementDefinition()->getAlias(), $collection->getElementDefinitions()->first()->getAlias());
    }

    public function test_first()
    {
        $this->elements->register(PlainElement::class)->install();

        $plainElement = PlainElement::create([]);

        /** @var Collection $collection */
        $collection = PlainElement::all();

        $this->assertSame($plainElement->getId(), $collection->first()->getId());
    }
}
