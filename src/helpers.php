<?php

use Click\Elements\Elements;
use Click\Elements\Exceptions\ElementNotDefinedException;
use Click\Elements\Exceptions\PropertyMissingException;
use Click\Elements\Exceptions\PropertyNotDefinedException;
use Click\Elements\Schema;

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

if (!function_exists('elements')) {
    /**
     * @return Elements
     */
    function elements()
    {
        return app(Elements::class);
    }
}

if (!function_exists('element')) {
    /**
     * @param string $type
     * @param array $attributes
     * @return Schema
     * @throws ElementNotDefinedException
     * @throws PropertyMissingException
     * @throws PropertyNotDefinedException
     */
    function element($type, $attributes = [])
    {
        return elements()->elements()->factory($type, $attributes);
    }
}
