<?php

declare(strict_types=1);

if (! function_exists('locale_route')) {
    /**
     * Generate a URL for a route in the current locale's named-route group.
     *
     * Locale codes like `pt_PT` are mapped to route prefixes like `pt-pt`.
     */
    function locale_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $prefix = str_replace('_', '-', strtolower(app()->getLocale()));

        return route($prefix.'.'.$name, $parameters, $absolute);
    }
}
