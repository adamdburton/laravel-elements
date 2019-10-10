<?php

namespace Click\Elements\Types;

use Click\Elements\Exceptions\Relation\RelationTypeNotValidException;
use Click\Elements\Tests\TestCase;

class RelationTypeTest extends TestCase
{
    public function test_validate_value()
    {
        $this->expectException(RelationTypeNotValidException::class);

        RelationType::validateValue('wefwef');
    }
}
