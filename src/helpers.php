<?php

use Click\Elements\Elements;

if (!function_exists('elements_path')) {
    /**
     * @return Elements::class
     */
    function elements()
    {
        try {
            return app(\Click\Elements\Elements::class);
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            // How
        }
    }
}

if (!function_exists('elements_path')) {
    /**
     * @param string $path
     * @return string
     */
    function elements_path($path = '')
    {
        return rtrim(realpath(__DIR__ . '/..'), '/') . ($path ? '/' . ltrim($path) : '');
    }
}
