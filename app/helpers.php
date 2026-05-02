<?php

declare(strict_types=1);

if (! function_exists('locale_route_for')) {
    /**
     * Generate a URL for a named route in the given locale's route group.
     *
     * Locale codes like `pt_PT` are mapped to route prefixes like `pt-pt`.
     */
    function locale_route_for(string $locale, string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $prefix = str_replace('_', '-', strtolower($locale));

        return route($prefix.'.'.$name, $parameters, $absolute);
    }
}

if (! function_exists('locale_route')) {
    /**
     * Generate a URL for a route in the current locale's named-route group.
     */
    function locale_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return locale_route_for(app()->getLocale(), $name, $parameters, $absolute);
    }
}
