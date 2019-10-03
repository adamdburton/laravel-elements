<?php

namespace Click\Elements\Tests;

use Click\Elements\Tests\TestCase;
use Click\Elements\Types\PropertyType;

class PropertyTypeTest extends TestCase
{
    public function test_is_valid_type()
    {
        $this->assertTrue(in_array('string', PropertyType::getTypes()));
        $this->assertFalse(in_array('something', PropertyType::getTypes()));
    }

    public function test_get_types()
    {
        $this->assertEquals(9, count(PropertyType::getTypes()));
    }
}
