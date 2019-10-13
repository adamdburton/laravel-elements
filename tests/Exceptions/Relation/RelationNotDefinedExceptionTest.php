<?php

namespace Click\Elements\Tests\Exceptions\Relation;

use Click\Elements\Exceptions\Relation\RelationNotDefinedException;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\TestCase;

class RelationNotDefinedExceptionTest extends TestCase
{
    public function test_exception()
    {
        $this->elements->register(PlainElement::class)->install();

        $element = new PlainElement();

        $this->expectException(RelationNotDefinedException::class);

        $element->getRelation('string');
    }
}
