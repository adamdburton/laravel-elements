<?php

namespace Click\Elements\Benchmarks;

use Click\Elements\Benchmarks\Assets\Elements\Author as AuthorElement;
use Click\Elements\Benchmarks\Assets\Elements\Book as BookElement;
use Click\Elements\Benchmarks\Assets\Models\Author as AuthorModel;

class UpdateBench extends Benchmark
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

        $this->element = AuthorElement::createRaw([
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
        $this->element->update([
            'name' => 'Seal Ntephenson',
            'born' => 2000
        ]);
    }

    public function bench_model()
    {
        $this->model->update([
            'name' => 'Seal Ntephenson',
            'born' => 2000
        ]);
    }
}
