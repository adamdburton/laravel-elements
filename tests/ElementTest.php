<?php

namespace Click\Elements\Tests;

use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Exceptions\Relation\RelationElementTypeInvalidException;
use Click\Elements\Exceptions\Relation\SingleRelationInvalidException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\Assets\RelatedElement;

/**
 * @covers \Click\Elements\Element
 */
class ElementTest extends TestCase
{
    public function test_create_element()
    {
        $this->elements->register(PlainElement::class)->install();

        $element = PlainElement::create([
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
        $this->elements->register(PlainElement::class)->install();

        $element = PlainElement::create([
            'string' => 'some string',
            'integer' => 123456789,
            'array' => ['some', 'array', 'data'],
        ]);

        $element->update([
            'string' => $string = 'some new string',
            'integer' => $integer = 987654321,
            'array' => $array = ['some', 'thing', 'else'],
        ]);

        $this->assertSame($string, $element->string);
        $this->assertSame($integer, $element->integer);
        $this->assertSame($array, $element->array);
    }

    public function test_invalid_relation()
    {
        $this->elements->register(RelatedElement::class)->install();

        $this->expectException(SingleRelationInvalidException::class);

        RelatedElement::create([
            'plainElement' => 6
        ]);
    }

    public function test_invalid_relations()
    {
        $this->elements->register(RelatedElement::class)->install();

        $this->expectException(ManyRelationInvalidException::class);

        RelatedElement::create([
            'plainElements' => [1, 3]
        ]);

//        $this->assertSame($plainElement->getPrimaryKey(), $relatedElement->plainElement);
    }

    public function test_single_relation()
    {
        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();

        $plainElement = PlainElement::create([
            'string' => 'test'
        ]);

        $relatedElement = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        dd($relatedElement);

        $this->assertSame($plainElement->getPrimaryKey(), $relatedElement->plainElement->getPrimaryKey());
    }

    public function test_many_relation()
    {
        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();

        $plainElements = [
            PlainElement::create([
                'string' => 'test'
            ]),
            PlainElement::create([
                'string' => 'test 2'
            ])
        ];

        $relatedElement = RelatedElement::create([
            'plainElements' => $plainElements
        ]);

        $this->assertSame($plainElements, $relatedElement->plainElements);
    }

    public function test_withs()
    {
        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();

        $plainElement = PlainElement::create([
            'string' => 'test'
        ]);

        $related = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        $element = RelatedElement::with('plainElement')->find($related->getPrimaryKey());

        $this->assertSame($plainElement, $element->plainElement);
    }

    public function test_querying_relations()
    {
        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();

        $plainElement1 = PlainElement::create([
            'string' => 'test'
        ]);

        $plainElement2 = PlainElement::create([
            'string' => 'test 2'
        ]);

        $related1 = RelatedElement::create([
            'plainElement' => $plainElement1
        ]);

        $related2 = RelatedElement::create([
            'relatedElement' => $related1,
            'plainElements' => [
                $plainElement1,
                $plainElement2
            ]
        ]);

        RelatedElement::where('relatedElement.plainElement.string', 'test 2')->get();

        RelatedElement::where('plainElements.string', 'test 2');
    }
}
