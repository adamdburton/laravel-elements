<?php

namespace Click\Elements\Commands;

use Click\Elements\Elements;
use Click\Elements\Exceptions\Element\ElementClassInvalidException;
use Click\Elements\Tests\Assets\PlainElement;
use Illuminate\Console\Command;

/**
 * php artisan elements:install
 */
class BenchmarkElements extends Command
{
    /**
     * @var string
     */
    protected $signature = 'elements:benchmark';

    /**
     * @var string
     */
    protected $description = 'TODO';

    /**
     * @var Elements
     */
    protected $elements;

    /**
     * @param Elements $elements
     */
    public function __construct(Elements $elements)
    {
        parent::__construct();

        $this->elements = $elements;
    }

    /**
     * @throws ElementClassInvalidException
     */
    public function handle()
    {
        $this->elements->register(PlainElement::class)->install();
    }
}
