<?php

namespace Click\Elements\Tests;

use BadMethodCallException;
use Click\Elements\Builder;
use Click\Elements\Element;
use Click\Elements\Exceptions\Property\PropertyValidationFailedException;
use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Click\Elements\Exceptions\Relation\ManyRelationInvalidException;
use Click\Elements\Exceptions\Relation\SingleRelationInvalidException;
use Click\Elements\Tests\Assets\GetterElement;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\Assets\RelatedElement;
use Click\Elements\Tests\Assets\ScopedElement;
use Click\Elements\Tests\Assets\SetterElement;
use Click\Elements\Tests\Assets\ValidationElement;

class ElementTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();
        $this->elements->register(ValidationElement::class)->install();
        $this->elements->register(ScopedElement::class)->install();
        $this->elements->register(SetterElement::class)->install();
        $this->elements->register(GetterElement::class)->install();
    }

    public function test_create()
    {
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

        $this->assertSame($string, $element->string);
        $this->assertSame($integer, $element->integer);
    }

    public function test_update()
    {
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
        $this->expectException(SingleRelationInvalidException::class);

        RelatedElement::create([
            'plainElement' => 'abc'
        ]);
    }

    public function test_invalid_relations()
    {
        $this->expectException(ManyRelationInvalidException::class);

        RelatedElement::create([
            'plainElements' => ['a', 'b']
        ]);
    }

    public function test_invalid_many_relation()
    {
        RelatedElement::create([
            'plainElements' => [2]
        ]);

        $this->expectException(ManyRelationInvalidException::class);

        RelatedElement::create([
            'plainElements' => 2
        ]);
    }

    public function test_single_relation()
    {
        $plainElement = PlainElement::create([
            'string' => 'test'
        ]);

        $relatedElement = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        $this->assertSame($plainElement->getPrimaryKey(), $relatedElement->plainElement->getPrimaryKey());
    }

    public function test_single_relation_null()
    {
        $element = new RelatedElement();

        $relatedElement = $element->plainElement;

        $this->assertNull($relatedElement);
    }

    public function test_many_relation()
    {
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

    public function test_many_relations_again()
    {
        $plainElement1 = PlainElement::create([]);
        $plainElement2 = PlainElement::create([]);

        $plainElementKeys = [$plainElement1->getPrimaryKey(), $plainElement2->getPrimaryKey()];

        $relatedElement = RelatedElement::create([
            'plainElements' => $plainElementKeys
        ]);

        $relatedElement = RelatedElement::find($relatedElement->getPrimaryKey());

        $plainElements = $relatedElement->plainElements;

        $this->assertSameSize($plainElementKeys, $plainElements);

        $keys = $plainElements->map(function (PlainElement $element) {
            return $element->getPrimaryKey();
        })->all();

        $this->assertSame($plainElementKeys, $keys);
    }

    public function test_many_relations_from_collection()
    {
        $relatedElement = RelatedElement::create([
            'plainElements' => $plainElements = [PlainElement::create([]), PlainElement::create([])]
        ]);

        $collection = $relatedElement->plainElements;

        $relatedElement = new RelatedElement();

        $relatedElement->plainElements = $collection;

        $this->assertSame($plainElements, $relatedElement->plainElements->all());
    }

    public function test_where_has_single_relation()
    {
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

    public function test_where_doesnt_have()
    {
        $relatedElement1 = RelatedElement::create([
            'plainElement' => PlainElement::create([
                'string' => 'abcdef'
            ])
        ]);

        $relatedElement1->plainElement;

        $relatedElement2 = RelatedElement::create([
            'plainElement' => PlainElement::create([
                'string' => 'uvwxyz'
            ])
        ]);

        $foundElement = RelatedElement::whereDoesntHave('plainElement', function(Builder $query) {
            $query->where('string', 'abcdef');
        })->first();

        $this->assertSame($relatedElement2->getPrimaryKey(), $foundElement->getPrimaryKey());
    }

    public function test_withs()
    {
        $plainElement = PlainElement::create([
            'string' => 'test'
        ]);

        $related = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        /** @var RelatedElement $element */
        $element = RelatedElement::with('plainElement')->find($related->getPrimaryKey());

        $this->assertTrue($element->hasRelationLoaded('plainElement'));
        $this->assertSame($plainElement->getPrimaryKey(), $element->plainElement->getPrimaryKey());

        $plainElement = PlainElement::create([
            'string' => 'test 123'
        ]);

        $related = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        $element = RelatedElement::with(['plainElement' => function (Builder $query) {
            $query->where('string', 'test 123');
        }])->find($related->getPrimaryKey());

        $this->assertTrue($element->hasRelationLoaded('plainElement'));
        $this->assertSame($plainElement->getPrimaryKey(), $element->plainElement->getPrimaryKey());
    }

    public function test_withs_again()
    {
        $plainElement1 = PlainElement::create([
            'string' => 'test'
        ]);

        $plainElement2 = PlainElement::create([
            'string' => 'test 2'
        ]);

        $related = RelatedElement::create([
            'plainElements' => [$plainElement1, $plainElement2]
        ]);

        /** @var RelatedElement $loadedElement */
        $loadedElement = RelatedElement::with('plainElements')->first();

        $this->assertTrue($loadedElement->hasRelationLoaded('plainElements'));
    }

    public function test_querying_relation_properties()
    {
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
        /** @var Element $plainElement */
        $plainElement = PlainElement::create(['string' => 'string']);

        $meta = $plainElement->getMeta();

        $this->assertSame(8, $meta['id']);
    }

    public function test_to_json()
    {
        /** @var Element $plainElement */
        $plainElement = PlainElement::create(['string' => $string = 'string']);

        $json = $plainElement->toJson();

        $this->assertSame(8, $json['meta']['id']);
        $this->assertSame($string, $json['attributes']['string']);
        $this->assertSame('string', $json['properties']['string']['type']);
    }

    public function test_all()
    {
        PlainElement::create(['string' => $string = 'string']);
        PlainElement::create(['integer' => $integer = -300]);
        PlainElement::create(['array' => $integer = ['one', 'two']]);

        $foundElements = PlainElement::all();

        $this->assertSame(3, $foundElements->count());
    }

    public function test_first_null()
    {
        $foundElement = PlainElement::first([]);

        $this->assertNull($foundElement);

        PlainElement::create([]);

        $foundElement = PlainElement::first([]);

        $this->assertNotNull($foundElement);
    }

    public function test_exists()
    {
        $found = PlainElement::exists();

        $this->assertFalse($found);

        PlainElement::create([]);

        $found = PlainElement::exists();

        $this->assertTrue($found);
    }

    public function test_scopes()
    {
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
        $plainElement = PlainElement::create([]);

        $relatedElement = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        $this->assertSame($plainElement->getPrimaryKey(), $relatedElement->plainElement()->first()->getPrimaryKey());
    }

    public function test_invalid_builder_call()
    {
        $plainElement = PlainElement::create([]);

        $this->expectException(BadMethodCallException::class);

        $plainElement->invalidBuilderMagicMethodCall();
    }

    public function test_to_sql()
    {
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
        $element = new SetterElement();

        $element->status = 'something';

        $this->assertSame('SOMETHING', $element->status);
    }

    public function test_getters()
    {
        $element = new GetterElement();

        $element->status = 'something';

        $this->assertSame('SOMETHING', $element->status);
    }

    public function test_validation()
    {
        $this->expectException(PropertyValidationFailedException::class);

        ValidationElement::create([]);
    }

    public function test_validation_again()
    {
        $element = new ValidationElement();

        $this->expectException(PropertyValidationFailedException::class);

        $element->string = '';
    }

    public function test_validation_again_again()
    {
        $this->expectException(PropertyValidationFailedException::class);

        ValidationElement::create([
            'string' => 'this_is_required',
            'email' => 'not_an_email'
        ]);
    }

    public function test_property_types()
    {
        $element = new ValidationElement();

        $this->expectException(PropertyValueInvalidException::class);

        $element->json = 123;
    }

    public function test_two_way_binding()
    {
        $this->expectNotToPerformAssertions();

        // TODO: https://git.clickdigitalsolutions.co.uk/internal/elements/issues/4
    }

    public function test_faked_properties()
    {
        $element = PlainElement::mock();

        $this->assertSame(PHP_INT_MAX, $element->unsigned_integer);
        $this->assertSame(PHP_INT_MIN, $element->integer);
    }
}
