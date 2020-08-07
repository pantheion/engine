<?php

use Pantheion\Engine\Container;

if (!function_exists('app')) 
{
    function app(string $key = null, array $params = [])
    {
        if(!is_null($key)) {
            return Container::get()->make($key, $params);
        }

        return Container::get();
    }
}

if (!function_exists('config')) 
{
    function config(string $key, $default = null)
    {
        $config = app('config');
        return $config->has($key) ? $config->get($key) : $default;
    }
}

if(!function_exists('env')) 
{
    function env(string $key, $default = null) 
    {
        return isset($_ENV) && isset($_ENV[$key]) ? $_ENV[$key] : $default;
    }
}