<?php

namespace Click\Elements\Tests;

use Click\Elements\Exceptions\Element\ElementNotInstalledException;
use Click\Elements\Exceptions\Attribute\AttributeValueTypeInvalidException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\Assets\RelatedElement;
use Click\Elements\Tests\Assets\ValidationElement;

class ElementsTest extends TestCase
{
    public function test_set_invalid_property()
    {
        $this->elements->register(ValidationElement::class)->install();

        $this->expectException(AttributeValueTypeInvalidException::class);

        ValidationElement::create(['string' => 123]);
    }

    public function test_get_element_definitions()
    {
        $this->elements->register(PlainElement::class)->install();
        $this->elements->register(RelatedElement::class)->install();
        $this->elements->register(ValidationElement::class)->install();

        $definitions = $this->elements->getElementDefinitions();

        // One extra for 'elementType' Element Type ;)

        $this->assertEquals(3, count($definitions));
    }
}
