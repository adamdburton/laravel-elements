<?php

namespace Click\Elements\Tests;

use Click\Elements\Tests\TestCase;
use Click\Elements\Types\AttributeType;

class AttributeTypeTest extends TestCase
{
    public function test_is_valid_type()
    {
        $this->assertTrue(in_array('string', AttributeType::getTypes()));
        $this->assertFalse(in_array('something', AttributeType::getTypes()));
    }

    public function test_get_types()
    {
        $this->assertEquals(10, count(AttributeType::getTypes()));
    }
}
