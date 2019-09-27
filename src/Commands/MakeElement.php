<?php

namespace Click\Elements\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * php artisan make:element {--model}
 */
class MakeElement extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:element';

    /**
     * @var string
     */
    protected $description = 'Create a new element class';

    /**
     * @var string
     */
    protected $type = 'Elements';

    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        if ($model = $this->option('model')) {
            if (!class_exists($model)) {
                $this->error($model . ' is not a valid class.');
                return;
            }

            $modelClass = class_basename($model);
            $namespace = $model;

            $class = str_replace('DummyModelClassNamespace', $namespace, $class);
            $class = str_replace('DummyModelClass', $modelClass, $class);
        }

        return $class;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('model')
            ? __DIR__ . '/stubs/element.model.stub'
            : __DIR__ . '/stubs/element.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Elements';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the element already exists'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Create an element set up for model binding'],
        ];
    }
}
