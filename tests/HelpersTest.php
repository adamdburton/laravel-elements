<?php

namespace Click\Elements\Tests;

use Click\Elements\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_elements_path()
    {
        $path = elements_path();

        $this->assertEquals(realpath(__DIR__ . '/..'), $path);

        $path = elements_path('database');

        $this->assertEquals(realpath(__DIR__ . '/../database'), $path);
    }
}
