<?php

namespace Click\Elements\Tests;

use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Property\PropertyValueInvalidException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\Assets\RelatedElement;
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

    public function test_get_element_definitions()
    {
        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();
        $this->elements->register(ValidationElement::class)->install();

        $definitions = $this->elements->getElementDefinitions();

        // One extra for 'elementType' Element Type ;)

        $this->assertEquals(4, count($definitions));
    }
}
