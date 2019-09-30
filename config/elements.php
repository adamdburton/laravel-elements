<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto Install Elements
    |--------------------------------------------------------------------------
    |
    | Should new and updated elements be automatically installed? This will
    | result in extra database queries on each request and should be turned
    | OFF in production.
    |
    */

    'auto_install' => env('ELEMENTS_AUTO_INSTALL', false)

];
