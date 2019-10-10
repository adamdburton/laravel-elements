<?php

use Click\Elements\Elements;
use Click\Elements\Exceptions\ElementsNotInstalledException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;

if (!function_exists('elements')) {
    /**
     * @return Elements
     * @throws BindingResolutionException
     */
    function elements()
    {
        return Container::getInstance()->make(Elements::class);
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
