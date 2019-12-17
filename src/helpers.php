<?php

use Click\Elements\Builder;
use Click\Elements\Elements;
use Click\Elements\Exceptions\Element\ElementNotRegisteredException;
use Illuminate\Contracts\Container\BindingResolutionException;

if (!function_exists('elements')) {
    /**
     * @return Elements
     */
    function elements()
    {
        try {
            return app(Elements::class);
        } catch (BindingResolutionException $e) {
            // Unreachable.
        }
    }
}

if (!function_exists('element')) {
    /**
     * @param $elementType
     * @return Builder
     * @throws ElementNotRegisteredException
     */
    function element($elementType)
    {
        return elements()->getElementDefinition($elementType)->getBuilder();
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
