<?php

namespace Click\Elements\Commands;

use Click\Elements\Elements;
use Illuminate\Console\Command;

/**
 * php artisan elements:install
 */
class InstallElements extends Command
{
    /**
     * @var string
     */
    protected $signature = 'elements:install';

    /**
     * @var string
     */
    protected $description = 'Installs elements and their properties into the database';

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

    public function handle()
    {
        $this->elements->install();
    }
}
