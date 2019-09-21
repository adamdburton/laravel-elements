<?php

namespace Click\Elements\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeElement extends GeneratorCommand
{
    /** @var string */
    protected $name = 'make:element';

    /** @var string */
    protected $description = 'Create a new element class';

    /** @var string */
    protected $type = 'Elements';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return elements_path('stubs/element.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Elements';
    }
}
