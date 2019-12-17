<?php

namespace Click\Elements\Tests;

use BadMethodCallException;
use Click\Elements\Builder;
use Click\Elements\Element;
use Click\Elements\Exceptions\Attribute\AttributeValidationFailedException;
use Click\Elements\Exceptions\Attribute\AttributeValueTypeInvalidException;
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

//        dd($element);

        $this->assertSame($string, $element->string);
        $this->assertSame($integer, $element->integer);
        $this->assertSame($array, $element->array);
    }

    public function test_update()
    {
        $element = PlainElement::create([
            'string' => 'some other string',
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
        $element = PlainElement::create([
            'string' => $string = 'some new string',
            'integer' => $integer = 987654321,
            'array' => $array = ['some', 'thing', 'else', 'too?']
        ]);

        $foundElement1 = PlainElement::where('string', $string)->first();

        $this->assertSame($element->getId(), $foundElement1->getId());

        $foundElement2 = PlainElement::where('integer', $integer)->first();

        $this->assertSame($element->getId(), $foundElement2->getId());

        $foundElement3 = PlainElement::where('array', $array)->first();

        $this->assertSame($element->getId(), $foundElement3->getId());
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
        $this->expectException(ManyRelationInvalidException::class);

        RelatedElement::create([
            'plainElements' => ['abc']
        ]);

        RelatedElement::create([
            'plainElements' => 2
        ]);
    }

    public function test_invalid_many_relation_again()
    {
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

        $this->assertSame($plainElement->getId(), $relatedElement->plainElement->getId());
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

        $originalIds = collect($plainElements)->map->getId()->all();
        $returnedIds = collect($relatedElement->plainElements)->map->getId()->all();

        $this->assertSame($originalIds, $returnedIds);

        $relatedElement = $relatedElement->update([
            'plainElements' => $plainElements
        ]);

        $returnedIds = collect($relatedElement->plainElements)->map->getId()->all();

        $this->assertSame($originalIds, $returnedIds);
    }

    public function test_many_relations_again()
    {
        $plainElement1 = PlainElement::create([]);
        $plainElement2 = PlainElement::create([]);

        $plainElementKeys = [$plainElement1->getId(), $plainElement2->getId()];

        $this->assertIsNumeric($plainElement1->getId());
        $this->assertIsNumeric($plainElement2->getId());

        $relatedElement = RelatedElement::create([
            'plainElements' => $plainElementKeys
        ]);

        $relatedElement = RelatedElement::find($relatedElement->getId());

        $plainElements = $relatedElement->plainElements;

        $this->assertSameSize($plainElementKeys, $plainElements);

        $keys = $plainElements->map(function (PlainElement $element) {
            return $element->getId();
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

        $this->assertSame($relatedElement->getId(), $returnedElement->getId());

        $returnedElement = RelatedElement::whereHas('plainElement', function (Builder $query) {
            $query->where('boolean', false);
        })->first();

        $this->assertSame($relatedElement->getId(), $returnedElement->getId());

        $relatedElement2 = RelatedElement::create([
            'relatedElement' => $relatedElement
        ]);

        $returnedElement = RelatedElement::whereHas('relatedElement', function (Builder $query) {
            $query->whereHas('plainElement', function (Builder $query) {
                $query->where('string', 'test');
            });
        })->first();

        $this->assertSame($relatedElement2->getId(), $returnedElement->getId());

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

        $this->assertSame($relatedElement3->getId(), $returnedElement->getId());
    }

    public function test_where_does_not_have()
    {
        $relatedElement1 = RelatedElement::create([
            'plainElement' => PlainElement::create([
                'string' => 'abcdef'
            ])
        ]);

        $relatedElement2 = RelatedElement::create([
            'plainElement' => PlainElement::create([
                'string' => 'abcdef'
            ])
        ]);

        $relatedElement3 = RelatedElement::create([
            'plainElement' => PlainElement::create([
                'string' => 'uvwxyz'
            ])
        ]);

        $foundElement = RelatedElement::whereDoesNotHave('plainElement', function (Builder $query) {
            $query->where('string', 'abcdef');
        })->first();

        $this->assertSame($relatedElement3->getId(), $foundElement->getId());
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
        $element = RelatedElement::with('plainElement')->find($related->getId());

        $this->assertTrue($element->hasRelationLoaded('plainElement'));
        $this->assertSame($plainElement->getId(), $element->plainElement->getId());

        $plainElement = PlainElement::create([
            'string' => 'test 123'
        ]);

        $related = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        $element = RelatedElement::with(['plainElement' => function (Builder $query) {
            $query->where('string', 'test 123');
        }])->find($related->getId());

        $this->assertTrue($element->hasRelationLoaded('plainElement'));
        $this->assertSame($plainElement->getId(), $element->plainElement->getId());
    }

    public function test_withs_again()
    {
        $plainElement1 = PlainElement::create([
            'string' => 'test'
        ]);

        $plainElement2 = PlainElement::create([
            'string' => 'test 2'
        ]);

        $relatedElement = RelatedElement::create([
            'plainElements' => [$plainElement1, $plainElement2]
        ]);

        /** @var RelatedElement $loadedElement */
        $loadedElement = RelatedElement::with('plainElements')->first();

        $this->assertTrue($loadedElement->hasRelationLoaded('plainElements'));
        $this->assertEquals(2, $loadedElement->getLoadedRelation('plainElements')->count());
    }

    public function test_querying_relation_properties()
    {
        $plainElement1 = PlainElement::create([
            'string' => $plain1String = 'testing abc'
        ]);

        $plainElement2 = PlainElement::create([
            'string' => $plain2String = 'testing xyz'
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

        $returnedElement = RelatedElement::where('plainElement.string', $plain1String)->first();

        $this->assertSame($related1->getId(), $returnedElement->getId());

        $returnedElement = RelatedElement::where('plainElements.string', $plain2String)->first();

        $this->assertSame($related2->getId(), $returnedElement->getId());

        $returnedElement = RelatedElement::where('plainElements.string', $plain1String)->first();

        $this->assertSame($related2->getId(), $returnedElement->getId());
    }

    public function test_meta()
    {
        /** @var Element $plainElement */
        $plainElement = PlainElement::create(['string' => 'string']);

        $meta = $plainElement->getMeta();

        $this->assertIsNumeric($meta['id']);
        $this->assertIsString($meta['type']);
    }

    public function test_to_json()
    {
        /** @var Element $plainElement */
        $plainElement = PlainElement::create(['string' => $string = 'string']);

        $json = $plainElement->toJson();

        $this->assertIsNumeric($json['meta']['id']);
        $this->assertIsArray($json['attributes']);
        $this->assertIsArray($json['values']);

        $this->assertSame($string, $json['attributes']['string']);
        $this->assertSame('string', $json['values']['string']['type']);
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

        $this->assertSame($enabledElement->getId(), $foundElement->getId());

        $foundElement = ScopedElement::disabled()->first();

        $this->assertSame($disabledElement->getId(), $foundElement->getId());

        $foundElement = ScopedElement::status('enabled')->first();

        $this->assertSame($enabledElement->getId(), $foundElement->getId());

        $foundElement = ScopedElement::status('disabled')->first();

        $this->assertSame($disabledElement->getId(), $foundElement->getId());

        $foundElement = ScopedElement::status('wefwefwfwf')->first();

        $this->assertNull($foundElement);
    }

    public function test_relation_call_method()
    {
        $plainElement = PlainElement::create([
            'string' => 'abc'
        ]);

        $relatedElement = RelatedElement::create([
            'plainElement' => $plainElement
        ]);

        $this->assertSame($plainElement->getId(), $relatedElement->plainElement()->first()->getId());
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
select * from `elements_entities` where `type` = relatedElement and exists (select * from `elements_entities` as `laravel_reserved_0` inner join `elements_values` on `laravel_reserved_0`.`id` = `elements_values`.`unsigned_integer_value` where `elements_entities`.`id` = `elements_values`.`entity_id` and `attribute_id` = ? and exists (select * from `elements_entities` inner join `elements_values` on `elements_entities`.`id` = `elements_values`.`unsigned_integer_value` where `laravel_reserved_0`.`id` = `elements_values`.`entity_id` and `attribute_id` = ? and exists (select * from `elements_attributes` inner join `elements_values` on `elements_attributes`.`id` = `elements_values`.`attribute_id` where `elements_entities`.`id` = `elements_values`.`entity_id` and `attribute_id` = ? and `string_value` = string)))
SQL;

        $expected = preg_replace('/laravel_reserved_\d+/', 'laravel_reserved_?', $sql);

        $actual = preg_replace('/laravel_reserved_\d+/', 'laravel_reserved_?', $query->toSql());
        $actual = preg_replace('/`attribute_id` = \d+/', '`attribute_id` = ?', $actual);

        $this->assertSame($expected, $actual);
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
        $this->expectException(AttributeValidationFailedException::class);

        ValidationElement::create([]);
    }

    public function test_validation_again()
    {
        $element = new ValidationElement();

        $this->expectException(AttributeValidationFailedException::class);

        $element->string = '';
    }

    public function test_validation_again_again()
    {
        $this->expectException(AttributeValidationFailedException::class);

        ValidationElement::create([
            'string' => 'this_is_required',
            'email' => 'not_an_email'
        ]);
    }

    public function test_attribute_validation()
    {
        $element = new ValidationElement();

        $this->expectException(AttributeValueTypeInvalidException::class);

        $element->json = 123;
    }

    public function test_two_way_binding()
    {
        $this->expectNotToPerformAssertions();

        // TODO: https://git.clickdigitalsolutions.co.uk/internal/elements/issues/4
    }

    public function test_faked_attributes()
    {
        $element = PlainElement::mock();

        $this->assertSame(PHP_INT_MAX, $element->unsigned_integer);
        $this->assertSame(PHP_INT_MIN, $element->integer);
    }
}
