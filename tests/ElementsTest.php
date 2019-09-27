<?php

namespace Click\Elements\Tests;

use Click\Elements\Exceptions\ElementTypeNotInstalledException;
use Click\Elements\Exceptions\ElementValidationFailed;
use Click\Elements\Exceptions\PropertyMissingException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\Assets\ValidationElement;

class ElementsTest extends TestCase
{
    protected $testElementInstalled = false;

    public function test_register_without_install()
    {
        $elementType = $this->elements->register(PlainElement::class);

        $this->expectException(ElementTypeNotInstalledException::class);

        PlainElement::create([]);
    }

    public function test_set_invalid_property()
    {
        $elementType = $this->elements->register(ValidationElement::class)->install();

        $this->expectException(ElementValidationFailed::class);

        ValidationElement::create(['wef' => 'ewfw']);
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
