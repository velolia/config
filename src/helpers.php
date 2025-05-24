<?php

if (!function_exists('config')) {
    function config(string $key, mixed $value = null): mixed
    {
        if (func_num_args() === 2) {
            return \Velolia\Config\Config::set($key, $value);
        }
        return \Velolia\Config\Config::get($key, $value);
    }
}