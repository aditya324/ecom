<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function edit(): View
    {
        return view('admin.business.edit', [
            'business' => Business::current(),
        ]);
    }

    public function update(BusinessRequest $request): RedirectResponse
    {
        Business::current()->update($request->safe()->only([
            'legal_name',
            'gstin',
            'address',
            'email',
            'instagram',
        ]));

        return redirect()
            ->route('admin.business.edit')
            ->with('status', 'Business details saved.');
    }
}
