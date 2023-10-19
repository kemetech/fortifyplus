<?php

namespace FortifyPlus;

class RoutePath
{
    /**
     * Get the route path for the given route name.
     *
     * @param  string  $routeName
     * @param  string  $default
     * @return string
     */
    public static function for(string $routeName, string $default)
    {
        return config('fortifyplus.paths.user'.$routeName) ?? $default;
    }

    /**
     * Get the route path for the given route name.
     *
     * @param  string  $routeName
     * @param  string  $default
     * @return string
     */
    public static function forAdmin(string $routeName, string $default)
    {
        return config('fortifyplus.paths.admin'.$routeName) ?? $default;
    }
}
