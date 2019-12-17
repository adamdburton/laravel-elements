<?php

namespace Click\Elements\Tests\Exceptions\Attribute;

use Click\Elements\Exceptions\Attribute\AttributeAlreadyDefinedException;
use Click\Elements\Exceptions\Attribute\AttributeKeyInvalidException;
use Click\Elements\Tests\Assets\BadKeyNameElement;
use Click\Elements\Tests\Assets\DuplicateKeyElement;
use Click\Elements\Tests\TestCase;

class AttributeKeyInvalidExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->expectException(AttributeKeyInvalidException::class);

        $this->elements->register(BadKeyNameElement::class);
    }
}
