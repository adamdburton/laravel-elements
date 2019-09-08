<?php

namespace Click\Elements\Facades;

use Illuminate\Support\Facades\Facade;

class Elements extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'elements';
    }
}
