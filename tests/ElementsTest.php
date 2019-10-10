<?php

namespace Click\Elements\Tests;

use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\Assets\ValidationElement;

class ElementsTest extends TestCase
{
    protected $testElementInstalled = false;

    public function test_register_without_install()
    {
        $this->elements->register(PlainElement::class);

        $this->expectException(ElementNotInstalledException::class);

        PlainElement::create([]);
    }

    public function test_set_invalid_property()
    {
        $this->elements->register(ValidationElement::class)->install();

        $this->expectException(PropertyValueInvalidException::class);

        ValidationElement::create(['string' => 123]);
    }

//    public function test_validation()
//    {
//        $elementType = $this->elements->register(PlainElement::class)->install();
//
//        $this->expectException(ElementValidationFailed::class);
//
//        PlainElement::create([
//            'string' => []
//        ]);
//    }
//
//    public function test_builder_where()
//    {
//        $this->elements->register(ValidationElement::class)->install();
//    }
}
