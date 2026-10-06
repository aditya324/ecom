<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\RedirectResponse;

trait ReturnsToFilteredIndex
{
    protected function filteredIndex(string $route, string $status): RedirectResponse
    {
        $query = array_filter(
            request()->query(),
            fn (mixed $value) => $value !== null && $value !== '',
        );

        return redirect()
            ->route($route, $query)
            ->with('status', $status);
    }
}
