<?php

use Click\Elements\Elements;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Illuminate\Contracts\Container\BindingResolutionException;

if (!function_exists('elements')) {
    /**
     * @return Elements
     * @throws ElementsNotInstalledException
     */
    function elements()
    {
        try {
            return app()->make(Elements::class);
        } catch (BindingResolutionException $e) {
            throw new ElementsNotInstalledException();
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
