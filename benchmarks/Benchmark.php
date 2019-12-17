<?php

namespace Click\Elements\Benchmarks;

use Click\Elements\Benchmarks\Assets\Elements\Author as AuthorElement;
use Click\Elements\Benchmarks\Assets\Elements\Book as BookElement;
use Click\Elements\Tests\Assets\PlainElement;
use Click\Elements\Tests\TestCase;

/**
 * @BeforeMethods({"setUp"})
 * @Revs(100)
 * @Iterations(5)
 */
abstract class Benchmark extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/assets/migrations');
        $this->withFactories(__DIR__ . '/assets/factories');

        elements()->register(AuthorElement::class)->install();
        elements()->register(BookElement::class)->install();
        elements()->register(PlainElement::class)->install();
    }

}
