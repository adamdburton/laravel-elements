<?php

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
