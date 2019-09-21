<?php

namespace Click\Elements\Tests\Services;

use Click\Elements\PropertyType;
use Click\Elements\Tests\TestCase;

class PropertyTypeTest extends TestCase
{
    public function test_is_valid_type()
    {
        $this->assertTrue(PropertyType::isValidType('string'));
        $this->assertFalse(PropertyType::isValidType('something'));
    }

    public function test_get_types()
    {
        $this->assertEquals(9, count(PropertyType::getTypes()));
    }
}
