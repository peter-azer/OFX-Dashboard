<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Service::orderBy('order')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_name' => 'required|string',
            'short_description' => 'required|string',
            'service_name_ar' => 'required|string',
            'short_description_ar' => 'required|string',
            'icon_url' => 'required|image|mimes:jpeg,png,jpg,webp,gif,svg|max:4096',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('icon_url')) {
            $path = $request->file('icon_url')->store('services', 'public');
            $validated['icon_url'] = URL::to(Storage::url($path));
        }

        return Service::create($validated);
    }

    /**
     * get each service with it's works
     */

    public function servicePage($serviceId = null)
    {
        try {
            if ($serviceId) {
                return Service::with('work')->where('id', $serviceId)->orderBy('order')->get();
            }
            return Service::with('works')->orderBy('order')->get();
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching services. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return $service;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'service_name' => 'required|string',
            'short_description' => 'required|string',
            'service_name_ar' => 'required|string',
            'short_description_ar' => 'required|string',
            'icon_url' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:4096',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('icon_url')) {
            if (!empty($service->icon_url)) {
                $oldPath = parse_url($service->icon_url, PHP_URL_PATH) ?? '';
                $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
            $path = $request->file('icon_url')->store('services', 'public');
            $validated['icon_url'] = URL::to(Storage::url($path));
        }

        $service->update($validated);

        return $service;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        if (!empty($service->icon_url)) {
            $oldPath = parse_url($service->icon_url, PHP_URL_PATH) ?? '';
            $old = ltrim(str_replace('/storage/', '', $oldPath), '/');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $service->delete();

        return response()->noContent();
    }
}
