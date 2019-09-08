<?php

use Click\Elements\Elements;
use Click\Elements\Models\Entity;

if (!function_exists('elements_path')) {
    /**
     * @param string $path
     * @return string
     */
    function elements_path($path = '')
    {
        return rtrim(realpath(__DIR__ . '../../'), '/') . ($path ? '/' . ltrim($path) : '');
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
     * @return Query
     */
    function element($type)
    {
        return Entity::with('properties')->type($type);
    }
}