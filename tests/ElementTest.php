<?php

namespace Click\Elements\Tests;

use Click\Elements\Builder;
use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
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
            'array' => ['some', 'array', 'data']
        ]);

        $element->update([
            'string' => $string = 'some new string',
            'integer' => $integer = 987654321,
            'array' => $array = ['some', 'thing', 'else']
        ]);

        $this->assertSame($string, $element->string);
        $this->assertSame($integer, $element->integer);
        $this->assertSame($array, $element->array);
    }

    public function test_where()
    {
        $this->elements->register(PlainElement::class)->install();

        $element = PlainElement::create([
            'string' => $string = 'some new string',
            'integer' => $integer = 987654321,
            'array' => $array = ['some', 'thing', 'else', 'too?']
        ]);

        $foundElement1 = PlainElement::where('string', $string)->first();

        $this->assertSame($element->getPrimaryKey(), $foundElement1->getPrimaryKey());

        $foundElement2 = PlainElement::where('integer', $integer)->first();

        $this->assertSame($element->getPrimaryKey(), $foundElement2->getPrimaryKey());

        $foundElement3 = PlainElement::where('array', $array)->first();

        $this->assertSame($element->getPrimaryKey(), $foundElement3->getPrimaryKey());
    }

    public function test_invalid_relation()
    {
        $this->elements->register(RelatedElement::class)->install();

        $this->expectException(SingleRelationInvalidException::class);

        RelatedElement::create([
            'plainElement' => 'abc'
        ]);
    }

    public function test_invalid_relations()
    {
        $this->elements->register(RelatedElement::class)->install();

        $this->expectException(ManyRelationInvalidException::class);

        RelatedElement::create([
            'plainElements' => ['a', 'b']
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

        $originalIds = collect($plainElements)->map->getPrimaryKey()->all();
        $returnedIds = collect($relatedElement->plainElements)->map->getPrimaryKey()->all();

        $this->assertSame($originalIds, $returnedIds);
    }

    public function test_where_has_single_relation()
    {
        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();

        $plainElement = PlainElement::create([
            'string' => 'test',
            'boolean' => false
        ]);

        $relatedElement = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        $returnedElement = RelatedElement::whereHas('plainElement', function (Builder $query) {
            $query->where('string', 'test');
        })->first();

        $this->assertSame($relatedElement->getPrimaryKey(), $returnedElement->getPrimaryKey());

        $returnedElement = RelatedElement::whereHas('plainElement', function (Builder $query) {
            $query->where('boolean', false);
        })->first();

        $this->assertSame($relatedElement->getPrimaryKey(), $returnedElement->getPrimaryKey());

        $relatedElement2 = RelatedElement::create([
            'relatedElement' => $relatedElement
        ]);

        $returnedElement = RelatedElement::whereHas('relatedElement', function (Builder $query) {
            $query->whereHas('plainElement', function (Builder $query) {
                $query->where('string', 'test');
            });
        })->first();

        $this->assertSame($relatedElement2->getPrimaryKey(), $returnedElement->getPrimaryKey());

        $relatedElement3 = RelatedElement::create([
            'relatedElement' => $relatedElement2
        ]);

        $returnedElement = RelatedElement::whereHas('relatedElement', function (Builder $query) {
            $query->whereHas('relatedElement', function (Builder $query) {
                $query->whereHas('plainElement', function (Builder $query) {
                    $query->where('string', 'test');
                });
            });
        })->first();

        $this->assertSame($relatedElement3->getPrimaryKey(), $returnedElement->getPrimaryKey());
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

        $this->assertSame($plainElement->getPrimaryKey(), $element->plainElement->getPrimaryKey());
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

        $returnedElement = RelatedElement::where('plainElement.string', 'test 2')->first();

        $this->assertSame($related1->getPrimaryKey(), $returnedElement->getPrimaryKey());

//        $returnedElement = RelatedElement::where('plainElements.string', 'test')->first();
//
//        $this->assertSame($related2->getPrimaryKey(), $returnedElement->getPrimaryKey());
    }
}
