<?php

namespace Click\Elements\Benchmarks;

use Click\Elements\Benchmarks\Assets\Elements\Author as AuthorElement;
use Click\Elements\Benchmarks\Assets\Models\Author as AuthorModel;

class WhereBench extends Benchmark
{
    /**
     * @var AuthorElement
     */
    protected $element;

    /**
     * @var AuthorModel
     */
    protected $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->element = AuthorElement::create([
            'name' => 'Neal Stephenson',
            'born' => 1959
        ]);

        $this->model = AuthorModel::create([
            'name' => 'Neal Stephenson',
            'born' => 1959,
        ]);
    }

    public function bench_element()
    {
        AuthorElement::where('born', 1959)->first();
    }

    public function bench_model()
    {
        AuthorModel::where('born', 1959)->first();
    }
}
