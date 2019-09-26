<?php

namespace Click\Elements\Tests;

use Click\Elements\Exceptions\ElementTypeNotInstalledException;
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

//    public function test_validation()
//    {
//        $elementType = $this->elements->register(ValidationElement::class)->install();
//
//        $this->expectException(ElementTypeNotInstalledException::class);
//
//        ValidationElement::create([]);
//    }
//
//    public function test_builder_where()
//    {
//        $this->elements->register(ValidationElement::class)->install();
//    }
}
