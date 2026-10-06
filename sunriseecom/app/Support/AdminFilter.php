<?php

namespace App\Support;

use Illuminate\Http\Request;

class AdminFilter
{
    public static function text(Request $request, string $key): string
    {
        return trim($request->string($key)->toString());
    }

    public static function choice(Request $request, string $key, array $allowed): string
    {
        $value = $request->string($key)->toString();

        return in_array($value, $allowed, true) ? $value : '';
    }

    public static function number(Request $request, string $key): ?float
    {
        if (! $request->filled($key) || ! is_numeric($request->input($key))) {
            return null;
        }

        return (float) $request->input($key);
    }

    public static function like(string $value): string
    {
        return '%'.addcslashes($value, '%_\\').'%';
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $defaults
     */
    public static function active(array $filters, array $defaults = []): bool
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (($defaults[$key] ?? null) === $value) {
                continue;
            }

            return true;
        }

        return false;
    }
}
