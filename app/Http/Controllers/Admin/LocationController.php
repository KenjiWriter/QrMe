<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/locations/Index', [
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Location::create($this->validateData($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Location created.')]);

        return back();
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $location->update($this->validateData($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Location updated.')]);

        return back();
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Location deleted.')]);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'building_number' => ['required', 'string', 'max:32'],
            'apartment_number' => ['nullable', 'string', 'max:32'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:16'],
            'country' => ['required', 'string', 'max:255'],
        ]);
    }
}
