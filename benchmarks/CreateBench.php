<?php

namespace Click\Elements\Benchmarks;

use Click\Elements\Benchmarks\Assets\Elements\Author as AuthorElement;
use Click\Elements\Benchmarks\Assets\Elements\Book as BookElement;
use Click\Elements\Benchmarks\Assets\Models\Author as AuthorModel;
use Click\Elements\Benchmarks\Assets\Models\Book as BookModel;
use Click\Elements\Tests\Assets\PlainElement;

class CreateBench extends Benchmark
{
    /**
     * @groups("basic")
     */
    public function bench_basic_element()
    {
        AuthorElement::create([
            'name' => 'Neal Stephenson',
            'born' => 1959
        ]);
    }

    /**
     * @groups("basic")
     */
    public function bench_basic_model()
    {
        AuthorModel::create([
            'name' => 'Neal Stephenson',
            'born' => 1959,
        ]);
    }

    /**
     * @groups("complex")
     */
    public function bench_complex_element()
    {
        PlainElement::mock()->create([]);
    }

    /**
     * @groups("complex")
     */
    public function bench_complex_model()
    {
        factory(AuthorModel::class)->create([]);
    }

    /**
     * @groups("related")
     */
    public function bench_related_elements()
    {
        $author = AuthorElement::create([
            'name' => 'Neal Stephenson',
            'born' => 1959
        ]);

        BookElement::create([
            'name' => 'Snow Crash',
            'released' => 1992,
            'author' => $author->getId()
        ]);
    }

    /**
     * @groups("related")
     */
    public function bench_related_models()
    {
        $author = AuthorModel::create([
            'name' => 'Neal Stephenson',
            'born' => 1959,
        ]);

        BookModel::create([
            'name' => 'Snow Crash',
            'released' => 1992,
            'author_id' => $author->id
        ]);
    }
}