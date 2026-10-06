<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        return view('wishlist.index', [
            'services' => (new Wishlist($request))->services(),
        ]);
    }

    public function toggle(Request $request, Service $service): RedirectResponse
    {
        abort_unless($service->is_active, 404);

        $saved = (new Wishlist($request))->toggle($service);

        return back()->with('status', $saved
            ? $service->name.' saved to your wishlist.'
            : $service->name.' removed from your wishlist.');
    }
}
