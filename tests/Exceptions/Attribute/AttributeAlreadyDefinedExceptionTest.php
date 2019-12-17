<?php

namespace Click\Elements\Tests\Exceptions\Attribute;

use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Tests\Assets\DuplicateKeyElement;
use Click\Elements\Tests\TestCase;

class AttributeAlreadyDefinedExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->expectException(AttributeAlreadyDefinedException::class);

        $this->elements->register(DuplicateKeyElement::class);
    }
}
