<?php

namespace Click\Elements\Tests;

use BadMethodCallException;
use Click\Elements\Builder;
use Click\Elements\Element;
use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Exceptions\Relation\SingleRelationInvalidException;
use Click\Elements\Tests\Assets\GetterElement;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\Assets\RelatedElement;
use Click\Elements\Tests\Assets\ScopedElement;
use Click\Elements\Tests\Assets\SetterElement;

class ElementTest extends TestCase
{
    public function test_create()
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

        $element = PlainElement::createRaw([
            'string' => $string = 123,
            'integer' => $integer = '123'
        ]);

        $this->assertSame((string)$string, $element->string);
        $this->assertSame((int)$integer, $element->integer);
    }

    public function test_update()
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

        $element->updateRaw([
            'string' => 123
        ]);

        $this->assertSame(123, $element->string);
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

        $relatedElement = $relatedElement->update([
            'plainElements' => $plainElements
        ]);

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

        $plainElement = PlainElement::create([
            'string' => 'test 123'
        ]);

        $related = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        $element = RelatedElement::with(['plainElement' => function (Builder $query) {
            $query->where('string', 'testing');
        }])->find($related->getPrimaryKey());

        $this->assertSame($plainElement->getPrimaryKey(), $element->plainElement->getPrimaryKey());
    }

    public function test_querying_relation_properties()
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

    public function test_meta()
    {
        $this->elements->register(PlainElement::class)->install();

        /** @var Element $plainElement */
        $plainElement = PlainElement::create(['string' => 'string']);

        $meta = $plainElement->getMeta();

        $this->assertSame(3, $meta['id']);
    }

    public function test_to_json()
    {
        $this->elements->register(PlainElement::class)->install();

        /** @var Element $plainElement */
        $plainElement = PlainElement::create(['string' => $string = 'string']);

        $json = $plainElement->toJson();

        $this->assertSame(3, $json['meta']['id']);
        $this->assertSame($string, $json['attributes']['string']);
        $this->assertSame('string', $json['properties']['string']['type']);
    }

    public function test_all()
    {
        $this->elements->register(PlainElement::class)->install();

        PlainElement::create(['string' => $string = 'string']);
        PlainElement::create(['integer' => $integer = -300]);
        PlainElement::create(['array' => $integer = ['one', 'two']]);

        $foundElements = PlainElement::all();

        $this->assertSame(3, $foundElements->count());
    }

    public function test_first_null()
    {
        $this->elements->register(PlainElement::class)->install();

        $foundElement = PlainElement::first([]);

        $this->assertNull($foundElement);

        PlainElement::create([]);

        $foundElement = PlainElement::first([]);

        $this->assertNotNull($foundElement);
    }

    public function test_exists()
    {
        $this->elements->register(PlainElement::class)->install();

        $found = PlainElement::exists();

        $this->assertFalse($found);

        PlainElement::create([]);

        $found = PlainElement::exists();

        $this->assertTrue($found);
    }

    public function test_scopes()
    {
        $this->elements->register(ScopedElement::class)->install();

        $disabledElement = ScopedElement::create([
            'enabled' => false,
            'status' => 'disabled'
        ]);

        $enabledElement = ScopedElement::create([
            'enabled' => true,
            'status' => 'enabled'
        ]);

        $foundElement = ScopedElement::enabled()->first();

        $this->assertSame($enabledElement->getPrimaryKey(), $foundElement->getPrimaryKey());

        $foundElement = ScopedElement::disabled()->first();

        $this->assertSame($disabledElement->getPrimaryKey(), $foundElement->getPrimaryKey());

        $foundElement = ScopedElement::status('enabled')->first();

        $this->assertSame($enabledElement->getPrimaryKey(), $foundElement->getPrimaryKey());

        $foundElement = ScopedElement::status('disabled')->first();

        $this->assertSame($disabledElement->getPrimaryKey(), $foundElement->getPrimaryKey());

        $foundElement = ScopedElement::status('wefwefwfwf')->first();

        $this->assertNull($foundElement);
    }

    public function test_relation_call_method()
    {
        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();

        $plainElement = PlainElement::create([]);

        $relatedElement = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        $this->assertSame($plainElement->getPrimaryKey(), $relatedElement->plainElement()->first()->getPrimaryKey());
    }

    public function test_invalid_builder_call()
    {
        $this->elements->register(PlainElement::class)->install();

        $plainElement = PlainElement::create([]);

        $this->expectException(BadMethodCallException::class);

        $plainElement->invalidBuilderMagicMethodCall();
    }

    public function test_to_sql()
    {
        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();

        $query = RelatedElement::whereHas('relatedElement', function (Builder $query) {
            $query->whereHas('plainElement', function (Builder $query) {
                $query->where('string', 'string');
            });
        });

        $sql = <<<SQL
select * from `elements_entities` where `type` = ? and exists (select * from `elements_entities` as `laravel_reserved_0` inner join `elements_entity_properties` on `laravel_reserved_0`.`id` = `elements_entity_properties`.`unsigned_integer_value` where `elements_entities`.`id` = `elements_entity_properties`.`entity_id` and `property_id` = ? and exists (select * from `elements_entities` inner join `elements_entity_properties` on `elements_entities`.`id` = `elements_entity_properties`.`unsigned_integer_value` where `laravel_reserved_0`.`id` = `elements_entity_properties`.`entity_id` and `property_id` = ? and exists (select * from `elements_properties` inner join `elements_entity_properties` on `elements_properties`.`id` = `elements_entity_properties`.`property_id` where `elements_entities`.`id` = `elements_entity_properties`.`entity_id` and `property_id` = ? and `string_value` = ?)))
SQL;

        $this->assertSame(strlen($sql), strlen($query->toSql()));
    }

    public function test_setters()
    {
        $this->elements->register(SetterElement::class)->install();

        $element = new SetterElement();

        $element->status = 'something';

        $this->assertSame('SOMETHING', $element->status);
    }

    public function test_getters()
    {
        $this->elements->register(GetterElement::class)->install();

        $element = new GetterElement();

        $element->status = 'something';

        $this->assertSame('SOMETHING', $element->status);
    }
}
