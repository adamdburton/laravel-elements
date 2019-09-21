<?php

namespace Click\Elements\Tests\Services;

use Click\Elements\Elements\Module;
use Click\Elements\Tests\TestCase;

class ElementTest extends TestCase
{
    public function test_element()
    {
        $module = Module::create([
            'name' => $name = 'some name',
            'description' => $description = 'some description',
            'version' => $version = 'some version',
        ]);

        $this->assertSame($name, $module->name);
        $this->assertSame($description, $module->description);
        $this->assertSame($version, $module->version);
    }
}
