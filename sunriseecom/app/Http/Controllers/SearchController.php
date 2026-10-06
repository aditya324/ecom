<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View|JsonResponse
    {
        $term = mb_substr(trim($request->string('q')->toString()), 0, 80);
        $services = $this->services($term, $request->expectsJson() ? 8 : 24);
        $plans = $this->plans($term, $request->expectsJson() ? 4 : 12);

        if ($request->expectsJson()) {
            return response()->json([
                'services' => $services->map(fn (Service $service) => [
                    'name' => $service->name,
                    'category' => $service->category?->name,
                    'url' => route('services.show', $service),
                ])->concat($plans->map(fn (Plan $plan) => [
                    'name' => $plan->name,
                    'category' => 'Package',
                    'url' => route('packages.show', $plan),
                ]))->values(),
            ]);
        }

        return view('search.index', [
            'term' => $term,
            'services' => $services,
            'plans' => $plans,
        ]);
    }

    /**
     * @return Collection<int, Service>
     */
    private function services(string $term, int $limit): Collection
    {
        if ($term === '') {
            return collect();
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return Service::query()
            ->where('is_active', true)
            ->where('is_listed', true)
            ->where(function (Builder $query) use ($like) {
                $query->whereLike('name', $like)
                    ->orWhereLike('short_description', $like);
            })
            ->with('category')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Plan>
     */
    private function plans(string $term, int $limit): Collection
    {
        if ($term === '') {
            return collect();
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return Plan::query()
            ->active()
            ->whereLike('name', $like)
            ->limit($limit)
            ->get();
    }
}
